<?php declare(strict_types=1);

namespace Backblaze\B2;

use Backblaze\B2\Exception\AccessForbiddenException;
use Backblaze\B2\Exception\BackblazeB2Exception;
use Backblaze\B2\Exception\BadAuthTokenException;
use Backblaze\B2\Exception\BadRequestException;
use Backblaze\B2\Exception\ExpiredTokenException;
use Backblaze\B2\Exception\InternalErrorException;
use Backblaze\B2\Exception\NotFoundException;
use Backblaze\B2\Exception\NotImplementedException;
use Backblaze\B2\Exception\RequestTimeoutException;
use Backblaze\B2\Exception\ServiceUnavailableException;
use Backblaze\B2\Exception\TooManyRequestsException;
use Backblaze\B2\Exception\UnauthorizedException;
use Backblaze\B2\Exception\UnsupportedException;
use Backblaze\B2\Model\ApplicationKey;
use Backblaze\B2\Model\Bucket;
use Backblaze\B2\Model\File;
use Backblaze\B2\Model\ListBucketResponse;
use Backblaze\B2\Model\ListFilesResponse;
use Backblaze\B2\Model\ListKeysResponse;
use Backblaze\B2\Model\UploadPartResponse;
use Backblaze\B2\Model\UploadPartUrl;
use Generator;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ApiClient
{

    private string $apiUrl;

    private string $downloadUrl;

    private ?string $accountId;

    private ?string $token;

    private ClientInterface $httpClient;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    private SerializerInterface $serializer;

    public function __construct(private string $applicationKeyId,
        private string $applicationKey, ClientInterface $httpClient = null,
        RequestFactoryInterface $requestFactory = null,
        StreamFactoryInterface $streamFactory = null
    ) {
        $this->token = null;
        $this->accountId = null;

        $this->httpClient = $httpClient ?: Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory
            ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory
            ?: Psr17FactoryDiscovery::findStreamFactory();

        $this->serializer = new Serializer(
            [new ArrayDenormalizer(),
             new ObjectNormalizer(null, null, null, new ReflectionExtractor())],
            [new JsonEncoder()]
        );
    }

    public function createKey(ApplicationKey $key): ApplicationKey
    {
        $response = $this->executeRequest(
            '/b2api/v2/b2_create_key', $key->toArray()
        );
        return $this->serializer->deserialize(
            $response->getBody(), ApplicationKey::class, 'json'
        );
    }

    public function deleteKey(string $applicationKeyId): ApplicationKey
    {
        $response = $this->executeRequest(
            '/b2api/v2/b2_delete_key', [
                'applicationKeyId' => $applicationKeyId
            ]
        );
        return $this->serializer->deserialize(
            $response->getBody(), ApplicationKey::class, 'json'
        );    }

    public function listKeys(int $maxKeyCount = 100): Generator
    {
        $startApplicationKeyId = null;

        do {
            $params = ['accountId'   => $this->accountId,
                       'maxKeyCount' => $maxKeyCount];
            if ($startApplicationKeyId !== null) {
                $params['startApplicationKeyId'] = $startApplicationKeyId;
            }
            $response = $this->executeRequest(
                '/b2api/v2/b2_list_keys', $params
            );

            $keys = $this->serializer->deserialize(
                (string)$response->getBody(), ListKeysResponse::class, 'json'
            );
            $startApplicationKeyId = $keys->getNextApplicationKeyId();
            foreach ($keys->getKeys() as $key) {
                yield $key;
            }
        } while ($startApplicationKeyId !== null);
    }

    private function executeRequest(string $uri, array $data = []
    ): ResponseInterface {
        if ($this->accountId === null) {
            $this->authorizeAccount();
            if (array_key_exists('accountId', $data)
                && $data['accountId'] === null
            ) {
                $data['accountId'] = $this->accountId;
            }
        }
        $request = $this->requestFactory
            ->createRequest('POST', $this->apiUrl . $uri)
            ->withHeader('Authorization', $this->token)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('User-Agent', 'slacker775/backblaze-b2-api')
            ->withBody($this->streamFactory->createStream(json_encode($data)));
        $response = $this->httpClient->sendRequest($request);
        if ($response->getStatusCode() >= 400) {
            $this->processErrors($response);
        }
        return $response;
    }

    public function authorizeAccount()
    {
        $authUrl = 'https://api.backblazeb2.com/b2api/v2/b2_authorize_account';

        $credential = base64_encode(
            $this->applicationKeyId . ':' . $this->applicationKey
        );
        $request = $this->requestFactory
            ->createRequest('GET', $authUrl)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Basic ' . $credential);
        $response = $this->httpClient->sendRequest($request);
        if ($response->getStatusCode() === 200) {
            $body = json_decode((string)$response->getBody(), true);
            $this->token = $body['authorizationToken'];
            $this->apiUrl = $body['apiUrl'];
            $this->downloadUrl = $body['downloadUrl'];
            $this->accountId = $body['accountId'];
        }
    }

    private function processErrors(ResponseInterface $response): void
    {
        $data = json_decode((string)$response->getBody(), true);
        $status = $data['status'];
        $code = $data['code'];
        $message = $data['message'];
        switch ($status) {
            case 400:
                throw new BadRequestException($message, $status);
            case 401:
                switch ($code) {
                    case 'bad_auth_token':
                        throw new BadAuthTokenException($message, $status);
                    case 'expired_auth_token':
                        throw new ExpiredTokenException($message, $status);
                    case 'unauthorized':
                        throw new UnauthorizedException($message, $status);
                    case 'unsupported':
                        throw new UnsupportedException($message, $status);
                }
                break;
            case 403:
                throw new AccessForbiddenException($message, $status);
            case 404:
                throw new NotFoundException($message, $status);
            case 408:
                throw new RequestTimeoutException($message, $status);
            case 429:
                throw new TooManyRequestsException($message, $status);
            case 500:
                throw new InternalErrorException($message, $status);
            case 503:
                throw new ServiceUnavailableException($message, $status);
            default:
                throw new BackblazeB2Exception($message, $status);
        }
    }

    public function createBucket(string $bucketName,
        string $bucketType = Bucket::BUCKET_TYPE_PRIVATE, array $options = []
    ): Bucket {
        $response = $this->executeRequest('/b2api/v2/b2_create_bucket', [
            'accountId'  => $this->accountId,
            'bucketName' => $bucketName,
            'bucketType' => $bucketType,
        ]);
        return $this->serializer->deserialize(
            $response->getBody(), Bucket::class, 'json'
        );
    }

    public function deleteBucket(string $bucketId): Bucket
    {
        $params = ['accountId' => $this->accountId, 'bucketId' => $bucketId];
        $response = $this->executeRequest(
            '/b2api/v2/b2_delete_bucket', $params
        );
        return $this->serializer->deserialize(
            $response->getBody(), Bucket::class, 'json'
        );
    }

    public function listBuckets(string $bucketName = null,
        string $bucketId = null, array $bucketTypes = ['all']
    ): array {
        $params = ['accountId'   => $this->accountId,
                   'bucketTypes' => $bucketTypes];
        if ($bucketId !== null) {
            $params['bucketId'] = $bucketId;
        }
        if ($bucketName !== null) {
            $params['bucketName'] = $bucketName;
        }

        $response = $this->executeRequest(
            '/b2api/v2/b2_list_buckets', $params
        );

        $buckets = $this->serializer->deserialize(
            (string)$response->getBody(), ListBucketResponse::class, 'json'
        );
        return $buckets->getBuckets();
    }

    public function updateBucket()
    {
        throw new NotImplementedException();
    }

    public function getFileByName(string $filename, string $bucketId,
    ): File {
        foreach (
            $this->listFilenames($bucketId, 100, $filename) as $result
        ) {
            if ($filename === $result->getFileName()) {
                return $result;
            }
        }
        throw new NotFoundException(sprintf("Not found: %s", $filename));
    }

    public function listFilenames(string $bucketId, int $maxFileCount = 100,
        string $prefix = '', string $delimiter = null
    ): Generator {
        $startFileName = null;

        do {
            $params = [
                'bucketId'     => $bucketId,
                'maxFileCount' => $maxFileCount,
                'prefix'       => $prefix,
                'delimiter'    => $delimiter,
            ];

            if ($startFileName !== null) {
                $params['startFileName'] = $startFileName;
            }

            $response = $this->executeRequest(
                '/b2api/v2/b2_list_file_names', $params
            );
            $files = $this->serializer->deserialize(
                (string)$response->getBody(), ListFilesResponse::class, 'json'
            );
            $startFileName = $files->getNextFileName();
            foreach ($files->getFiles() as $file) {
                yield $file;
            }
        } while ($startFileName !== null);
    }

    public function listFileVersions(string $bucketId, int $maxFileCount = 100,
        string $prefix = '', string $delimiter = null
    ) {
        $startFileName = null;
        $startFileId = null;

        do {
            $params = [
                'bucketId'     => $bucketId,
                'maxFileCount' => $maxFileCount,
                'prefix'       => $prefix,
                'delimiter'    => $delimiter,
            ];

            if ($startFileName !== null) {
                $params['startFileName'] = $startFileName;
                $params['startFileId'] = $startFileId;
            }

            $response = $this->executeRequest(
                '/b2api/v2/b2_list_file_versions', $params
            );
            $files = $this->serializer->deserialize(
                (string)$response->getBody(), ListFilesResponse::class, 'json'
            );
            $startFileName = $files->getNextFileName();
            $startFileId = $files->getNextFileId();
            foreach ($files->getFiles() as $file) {
                yield $file;
            }
        } while ($startFileName !== null);
    }

    public function copyFile(string $sourceId, string $destFilename,
        string $destBucketId = null
    ): File {
        $response = $this->executeRequest(
            '/b2api/v2/b2_copy_file',
            [
                'sourceFileId' => $sourceId,
                'fileName'     => $destFilename,
            ]
        );
        return $this->serializer->deserialize(
            $response->getBody(), File::class, 'json'
        );
    }

    public function copyPart()
    {
        throw new NotImplementedException();
    }

    public function hideFile(string $bucketId, string $fileName): File
    {
        $response = $this->executeRequest(
            '/b2api/v2/b2_hide_file',
            [
                'bucketId' => $bucketId,
                'fileName' => $fileName,
            ]
        );
        return $this->serializer->deserialize(
            $response->getBody(), File::class, 'json'
        );
    }

    public function deleteFile(string $fileId = null, string $filename = null,
        string $bucketId = null, bool $allVersions = false
    ): void {
        if ($fileId !== null) {
            $this->deleteSingleFile($fileId, $bucketId);
        } elseif ($filename !== null && $bucketId !== null) {
            foreach (
                $this->getFileByPrefix($filename, $bucketId) as $file
            ) {
                if ($file->getFileId() !== null) {
                    $this->deleteSingleFile(
                        $file->getFileId(), $bucketId, $file->getFileName()
                    );
                } else {
                    if ($file->getAction() === 'folder') {
                        /* Recurse through directories */
                        $this->deleteFile(
                            null, $file->getFileName(), $bucketId
                        );
                    }
                }
            }
        } else {
            throw new InvalidArgumentException(
                'Either fileId or filename and bucketId must be specified'
            );
        }
    }

    private function deleteSingleFile(string $fileId, string $bucketId,
        string $filename = null
    ) {
        if ($filename === null) {
            if (($info = $this->getFileInfo($fileId)) !== null) {
                $filename = $info->getFileName();
            } else {
                throw new NotFoundException('File does not exist');
            }
        }

        $response = $this->executeRequest(
            '/b2api/v2/b2_delete_file_version',
            [
                'fileId'   => $fileId,
                'fileName' => $filename,
            ]
        );
    }

    public function getFileInfo(string $fileId): File
    {
        $response = $this->executeRequest(
            '/b2api/v2/b2_get_file_info', ['fileId' => $fileId]
        );
        return $this->serializer->deserialize(
            $response->getBody(), File::class, 'json'
        );
    }

    public function getFileByPrefix(string $prefix, string $bucketId,
        string $delimiter = '/'
    ): Generator {
        foreach (
            $this->listFilenames($bucketId, 100, $prefix, $delimiter) as $result
        ) {
            if (str_starts_with($result->getFileName(), $prefix)) {
                yield $result;
            }
        }
    }

    public function downloadFileById(string $id)
    {
        $request = $this->requestFactory
            ->createRequest(
                'GET',
                $this->downloadUrl . '/b2api/v2/b2_download_file_by_id?fileId='
                . $id
            )
            ->withHeader('Content-Type', 'application/json');
        throw new NotImplementedException();
        return $this->httpClient->sendRequest($request);
    }

    public function downloadFileByName(string $filename, string $bucketName
    ): StreamInterface {
        $url = sprintf(
            '%s/file/%s/%s', $this->downloadUrl, $bucketName, $filename
        );
        $request = $this->requestFactory
            ->createRequest('GET', $url)
            ->withHeader('Authorization', $this->token)
            ->withHeader('User-Agent', 'slacker775/backblaze-b2-api');
        $response = $this->httpClient->sendRequest($request);
        if ($response->getStatusCode() >= 400) {
            $this->processErrors($response);
        }
        return $response->getBody();
    }

    public function uploadFile(string $filename, string $bucketId, $content,
        array $options = []
    ): File {
        $uploadParams = $this->getUploadUrl($bucketId);

        if (is_resource($content)) {
            $context = hash_init('sha1');
            hash_update_stream($context, $content);
            $hash = hash_final($context);
            $size = fstat($content)['size'];
            $lastModified = fstat($content)['mtime'] * 1000;
            rewind($content);
            $stream = $this->streamFactory->createStreamFromResource($content);
        } else {
            $hash = sha1($content);
            $size = strlen($content);
            $lastModified = round(microtime(true) * 1000);
            $stream = $this->streamFactory->createStream($content);
        }
        $request = $this->requestFactory
            ->createRequest('POST', $uploadParams['uploadUrl'])
            ->withHeader('Authorization', $uploadParams['authorizationToken'])
            ->withHeader('User-Agent', 'slacker775/backblaze-b2-api')
            ->withHeader('X-Bz-File-Name', urlencode($filename))
            ->withHeader('Content-Type', $options['contentType'] ?? 'b2/x-auto')
            ->withHeader('Content-Length', $size)
            ->withHeader('X-Bz-Content-Sha1', $hash)
            ->withHeader('X-Bz-Info-src_last_modified_millis', $lastModified)
            ->withBody($stream);
        $response = $this->httpClient->sendRequest($request);
        if ($response->getStatusCode() >= 400) {
            $this->processErrors($response);
        }
        return $this->serializer->deserialize(
            $response->getBody(), File::class, 'json'
        );
    }

    public function getUploadUrl(string $bucketId): array
    {
        $response = $this->executeRequest(
            '/b2api/v2/b2_get_upload_url', ['bucketId' => $bucketId]
        );
        return json_decode((string)$response->getBody(), true);
    }

    public function startLargeFile(string $fileName, string $bucketId,
        string $contentType, array $fileInfo = []
    ): File {
        $response = $this->executeRequest(
            '/b2api/v2/b2_start_large_file', [
                'bucketId'    => $bucketId,
                'fileName'    => $fileName,
                'contentType' => $contentType,
                'fileInfo'    => $fileInfo,
            ]
        );
        return $this->serializer->deserialize(
            $response->getBody(), File::class, 'json'
        );
    }

    public function getUploadPartUrl(string $fileId)
    {
        $response = $this->executeRequest(
            '/b2api/v2/b2_get_upload_part_url', [
                'fileId' => $fileId,
            ]
        );
        return $this->serializer->deserialize(
            $response->getBody(), UploadPartUrl::class, 'json'
        );
    }

    /**
     * @param string      $url
     * @param string      $token
     * @param int         $part
     * @param int         $contentLength
     * @param string|resource       $content
     * @param string|null $checksum
     *
     * @return \Backblaze\B2\Model\UploadPartResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function uploadPart(string $url, string $token, int $part,
        int $contentLength, mixed $content, string $checksum = null
    ): UploadPartResponse {
        $request = $this->requestFactory
            ->createRequest('POST', $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', $token)
            ->withHeader('X-Bz-Part-Number', $part);
        if(is_resource($content) === true) {
            $data = fread($content,$contentLength);
            $hash = sha1($data);
            $length = strlen($data);
            $request->withHeader('Content-Length', $length)
                ->withHeader('X-Bz-Content-Sha1', $hash)
                ->withBody($data);
        } else {
            $request->withHeader('Content-Length', $contentLength)
                ->withHeader('X-Bz-Content-Sha1', $checksum)
                ->withBody($content);
        }
        $response = $this->httpClient->sendRequest($request);
        return $this->serializer->deserialize(
            $response->getBody(), UploadPartResponse::class, 'json'
        );
    }

    /**
     * @param string $fileId    - ID returned by startLargeFile
     * @param array  $sha1Array - array of hex sha1 checksums for each
     *                          part of the upload.  Part 1 of the upload
     *                          corresponds to index 0 of sha1Array
     *
     */
    public function finishLargeFile(string $fileId, array $sha1Array): File
    {
        $response = $this->executeRequest(
            '/b2api/v2/b2_finish_large_file', [
                'fileId'        => $fileId,
                'partSha1Array' => $sha1Array,
            ]
        );
        return $this->serializer->deserialize(
            $response->getBody(), File::class, 'json'
        );
    }

    public function cancelLargeFile(string $fileId): array
    {
        $response = $this->executeRequest(
            '/b2api/v2/b2_cancel_large_file', [
                'fileId' => $fileId,
            ]
        );
        return json_decode((string)$response->getBody(), true);
    }

    public function listUnfinishedLargeFiles(string $bucketId,
        int $maxFileCount = 100, string $namePrefix = null
    ) {
        throw new NotImplementedException();
    }

    public function updateFileRetention()
    {
        throw new NotImplementedException();
    }

    public function updateLegalHold()
    {
        throw new NotImplementedException();
    }

    public function getDownloadAuthorization(string $path, string $bucketId,
        int $validity = 60
    ): string {
        $response = $this->executeRequest(
            '/b2api/v2/b2_get_download_authorization', [
            'bucketId'               => $bucketId,
            'fileNamePrefix'         => $path,
            'validDurationInSeconds' => $validity,
        ]
        );

        $result = json_decode((string)$response->getBody(), true);
        return $result['authorizationToken'];
    }

    public function createDownloadUrl(string $path, string $bucketName,
        string $authToken
    ): string {
        return sprintf(
            "%s/file/%s/%s?Authorization=%s", $this->downloadUrl, $bucketName,
            $path, $authToken
        );
    }

}