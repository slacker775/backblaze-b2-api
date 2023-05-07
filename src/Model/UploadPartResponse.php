<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

use DateTime;
use DateTimeInterface;

class UploadPartResponse
{

    private string $fileId;

    private int $partNumber;

    private int $contentLength;

    private string $contentSha1;

    private string $contentMd5;

    private BucketEncryptionSetting $serverSideEncryption;

    private DateTimeInterface $uploadTimestamp;

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function setFileId(string $fileId): UploadPartResponse
    {
        $this->fileId = $fileId;
        return $this;
    }

    public function getPartNumber(): int
    {
        return $this->partNumber;
    }

    public function setPartNumber(int $partNumber): UploadPartResponse
    {
        $this->partNumber = $partNumber;
        return $this;
    }

    public function getContentLength(): int
    {
        return $this->contentLength;
    }

    public function setContentLength(int $contentLength): UploadPartResponse
    {
        $this->contentLength = $contentLength;
        return $this;
    }

    public function getContentSha1(): string
    {
        return $this->contentSha1;
    }

    public function setContentSha1(string $contentSha1): UploadPartResponse
    {
        $this->contentSha1 = $contentSha1;
        return $this;
    }

    public function getContentMd5(): string
    {
        return $this->contentMd5;
    }

    public function setContentMd5(string $contentMd5): UploadPartResponse
    {
        $this->contentMd5 = $contentMd5;
        return $this;
    }

    public function getServerSideEncryption(): BucketEncryptionSetting
    {
        return $this->serverSideEncryption;
    }

    public function setServerSideEncryption(BucketEncryptionSetting $serverSideEncryption
    ): UploadPartResponse {
        $this->serverSideEncryption = $serverSideEncryption;
        return $this;
    }

    public function getUploadTimestamp(): DateTimeInterface
    {
        return $this->uploadTimestamp;
    }

    public function setUploadTimestamp(string|int|DateTimeInterface $uploadTimestamp = null
    ): UploadPartResponse {
        if (is_int($uploadTimestamp)) {
            $this->uploadTimestamp = new DateTime(
                '@' . $uploadTimestamp / 1000
            );
        } else {
            if ($uploadTimestamp instanceof DateTimeInterface) {
                $this->uploadTimestamp = $uploadTimestamp;

            }
        }

        return $this;
    }

}