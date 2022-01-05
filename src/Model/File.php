<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class File
{
    private string $accountId;

    private string $action;

    private string $bucketId;

    private int $contentLength;

    private string $contentSha1;

    private string $contentMd5;

    private string $contentType;

    private string $fileId;

    private $fileInfo;

    private string $fileName;

    private $fileRetention;

    private $legalHold;

    private $serverSideEncryption;

    private $uploadTimestamp;

    /**
     * @return string
     */
    public function getAccountId(): string
    {
        return $this->accountId;
    }

    /**
     * @param string $accountId
     *
     * @return File
     */
    public function setAccountId(string $accountId): File
    {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return File
     */
    public function setAction(string $action): File
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getBucketId(): string
    {
        return $this->bucketId;
    }

    /**
     * @param string $bucketId
     *
     * @return File
     */
    public function setBucketId(string $bucketId): File
    {
        $this->bucketId = $bucketId;
        return $this;
    }

    /**
     * @return int
     */
    public function getContentLength(): int
    {
        return $this->contentLength;
    }

    /**
     * @param int $contentLength
     *
     * @return File
     */
    public function setContentLength(int $contentLength): File
    {
        $this->contentLength = $contentLength;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentSha1(): string
    {
        return $this->contentSha1;
    }

    /**
     * @param string $contentSha1
     *
     * @return File
     */
    public function setContentSha1(string $contentSha1): File
    {
        $this->contentSha1 = $contentSha1;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentMd5(): string
    {
        return $this->contentMd5;
    }

    /**
     * @param string $contentMd5
     *
     * @return File
     */
    public function setContentMd5(string $contentMd5): File
    {
        $this->contentMd5 = $contentMd5;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     *
     * @return File
     */
    public function setContentType(string $contentType): File
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileId(): string
    {
        return $this->fileId;
    }

    /**
     * @param string $fileId
     *
     * @return File
     */
    public function setFileId(string $fileId): File
    {
        $this->fileId = $fileId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * @param mixed $fileInfo
     *
     * @return File
     */
    public function setFileInfo($fileInfo)
    {
        $this->fileInfo = $fileInfo;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     *
     * @return File
     */
    public function setFileName(string $fileName): File
    {
        $this->fileName = $fileName;
        return $this;
    }


}