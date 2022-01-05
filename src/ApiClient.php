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
use Backblaze\B2\Model\Bucket;
use Backblaze\B2\Model\File;
use Backblaze\B2\Model\ListBucketResponse;
use Backblaze\B2\Model\ListFilesResponse;
use Backblaze\B2\Model\ListKeysResponse;
use Generator;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
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

    public function createKey()
    {
        throw new NotImplementedException();
    }

    public function deleteKey()
    {
        throw new NotImplementedException();
    }

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
        if($this->accountId === null) {
            $this->authorizeAccount();
            if(array_key_exists('accountId', $data) && $data['accountId'] === null) {
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

    public function createBucket(string $bucketName, string $bucketType = Bucket::BUCKET_TYPE_PRIVATE, array $options = []): Bucket
    {
        $response = $this->executeRequest('/b2api/v2/b2_create_bucket',[
            'accountId' => $this->accountId,
            'bucketName' => $bucketName,
            'bucketType' => $bucketType
        ]);
        return $this->serializer->deserialize($response->getBody(), Bucket::class, 'json');
    }

    public function deleteBucket(string $bucketId): Bucket
    {
        $params = ['accountId' => $this->accountId, 'bucketId' => $bucketId];
        $response = $this->executeRequest(
            '/b2api/v2/b2_delete_bucket', $params
        );
        return $this->serializer->deserialize($response->getBody(), Bucket::class, 'json');
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

    public function getFileInfo(string $fileId): File
    {
        $response = $this->executeRequest(
            '/b2api/v2/b2_get_file_info', ['fileId' => $fileId]
        );
        return $this->serializer->deserialize(
            $response->getBody(), File::class, 'json'
        );
    }

    public function copyFile()
    {
        throw new NotImplementedException();
    }

    public function copyPart()
    {
        throw new NotImplementedException();
    }

    public function hideFile(string $bucketId, string $fileName)
    {
        $response = $this->executeRequest(
            '/b2api/v2/b2_hide_file',
            ['bucketId' => $bucketId, 'fileName' => $fileName]
        );

        throw new NotImplementedException();
    }

    public function downloadFileById(string $id)
    {
        $request = $this->requestFactory
            ->createRequest(
                'GET', $this->downloadUrl . '/b2api/v2/b2_download_file_by_id?fileId=' . $id
            )
            ->withHeader('Content-Type', 'application/json');
        throw new NotImplementedException();
        return $this->httpClient->sendRequest($request);
    }

    public function downloadFileByName()
    {
        throw new NotImplementedException();
    }

    public function uploadFile()
    {
        throw new NotImplementedException();
    }

    public function uploadPart()
    {
        throw new NotImplementedException();
    }

    public function startLargeFile()
    {
        throw new NotImplementedException();
    }

    public function finishLargeFile()
    {
        throw new NotImplementedException();
    }

    public function cancelLargeFile()
    {
        throw new NotImplementedException();
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

}