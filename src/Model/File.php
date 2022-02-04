<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

use DateTime;
use DateTimeInterface;

class File
{

    private string $accountId;

    private string $action;

    private string $bucketId;

    private int $contentLength;

    private ?string $contentSha1;

    private ?string $contentMd5;

    private ?string $contentType;

    private ?string $fileId;

    private array $fileInfo;

    private string $fileName;

    private FileLockConfiguration $fileRetention;

    private FileLockConfiguration $legalHold;

    private BucketEncryptionSetting $serverSideEncryption;

    private ?DateTimeInterface $uploadTimestamp;

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function setAccountId(string $accountId): File
    {
        $this->accountId = $accountId;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): File
    {
        $this->action = $action;
        return $this;
    }

    public function getBucketId(): string
    {
        return $this->bucketId;
    }

    public function setBucketId(string $bucketId): File
    {
        $this->bucketId = $bucketId;
        return $this;
    }

    public function getContentLength(): int
    {
        return $this->contentLength;
    }

    public function setContentLength(int $contentLength): File
    {
        $this->contentLength = $contentLength;
        return $this;
    }

    public function getContentSha1(): ?string
    {
        return $this->contentSha1;
    }

    public function setContentSha1(?string $contentSha1): File
    {
        $this->contentSha1 = $contentSha1;
        return $this;
    }

    public function getContentMd5(): ?string
    {
        return $this->contentMd5;
    }

    public function setContentMd5(?string $contentMd5): File
    {
        $this->contentMd5 = $contentMd5;
        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): File
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    public function setFileId(?string $fileId): File
    {
        $this->fileId = $fileId;
        return $this;
    }

    public function getFileInfo(): array
    {
        return $this->fileInfo;
    }

    public function setFileInfo(array $fileInfo): File
    {
        $this->fileInfo = $fileInfo;
        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): File
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getUploadTimestamp(): ?DateTimeInterface
    {
        return $this->uploadTimestamp;
    }

    public function setUploadTimestamp(string|int|DateTimeInterface $uploadTimestamp = null
    ): File {
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

    public function getServerSideEncryption(): BucketEncryptionSetting
    {
        return $this->serverSideEncryption;
    }

    public function setServerSideEncryption(BucketEncryptionSetting $serverSideEncryption
    ): File {
        $this->serverSideEncryption = $serverSideEncryption;
        return $this;
    }

    public function getFileRetention(): FileLockConfiguration
    {
        return $this->fileRetention;
    }

    public function setFileRetention(FileLockConfiguration $fileRetention): File
    {
        $this->fileRetention = $fileRetention;
        return $this;
    }

    public function getLegalHold(): FileLockConfiguration
    {
        return $this->legalHold;
    }

    public function setLegalHold(FileLockConfiguration $legalHold): File
    {
        $this->legalHold = $legalHold;
        return $this;
    }

}