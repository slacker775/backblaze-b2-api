<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class Bucket
{
    const BUCKET_TYPE_PUBLIC = 'allPublic';

    const BUCKET_TYPE_PRIVATE = 'allPrivate';

    private string $accountId;

    private string $bucketId;

    private string $bucketName;

    private string $bucketType;

    private $bucketInfo;

    private array $corsRules;

    private $fileLockConfiguration;

    private BucketEncryptionConfig $defaultServerSideEncryption;

    private array $lifeCycleRules;

    private int $revision;

    private array $options;

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
     * @return Bucket
     */
    public function setAccountId(string $accountId): Bucket
    {
        $this->accountId = $accountId;
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
     * @return Bucket
     */
    public function setBucketId(string $bucketId): Bucket
    {
        $this->bucketId = $bucketId;
        return $this;
    }

    /**
     * @return string
     */
    public function getBucketName(): string
    {
        return $this->bucketName;
    }

    /**
     * @param string $bucketName
     *
     * @return Bucket
     */
    public function setBucketName(string $bucketName): Bucket
    {
        $this->bucketName = $bucketName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBucketType(): string
    {
        return $this->bucketType;
    }

    /**
     * @param string $bucketType
     *
     * @return Bucket
     */
    public function setBucketType(string $bucketType): Bucket
    {
        $this->bucketType = $bucketType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBucketInfo()
    {
        return $this->bucketInfo;
    }

    /**
     * @param mixed $bucketInfo
     *
     * @return Bucket
     */
    public function setBucketInfo($bucketInfo)
    {
        $this->bucketInfo = $bucketInfo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileLockConfiguration()
    {
        return $this->fileLockConfiguration;
    }

    /**
     * @param mixed $fileLockConfiguration
     *
     * @return Bucket
     */
    public function setFileLockConfiguration($fileLockConfiguration)
    {
        $this->fileLockConfiguration = $fileLockConfiguration;
        return $this;
    }

    /**
     * @return \Backblaze\B2\Model\BucketEncryptionConfig
     */
    public function getDefaultServerSideEncryption(): BucketEncryptionConfig
    {
        return $this->defaultServerSideEncryption;
    }

    /**
     * @param \Backblaze\B2\Model\BucketEncryptionConfig $defaultServerSideEncryption
     *
     * @return Bucket
     */
    public function setDefaultServerSideEncryption(BucketEncryptionConfig $defaultServerSideEncryption
    ): Bucket {
        $this->defaultServerSideEncryption = $defaultServerSideEncryption;
        return $this;
    }

    /**
     * @return int
     */
    public function getRevision(): int
    {
        return $this->revision;
    }

    /**
     * @param int $revision
     *
     * @return Bucket
     */
    public function setRevision(int $revision): Bucket
    {
        $this->revision = $revision;
        return $this;
    }


}