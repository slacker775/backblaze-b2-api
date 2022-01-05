<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class ApplicationKey
{
    private string $accountId;

    private string $applicationKeyId;

    private string $bucketId;

    private array $capabilities;

    private ?string $expirationTimestamp;

    private string $keyName;

    private ?string $namePrefix;

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
     * @return ApplicationKey
     */
    public function setAccountId(string $accountId): ApplicationKey
    {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationKeyId(): string
    {
        return $this->applicationKeyId;
    }

    /**
     * @param string $applicationKeyId
     *
     * @return ApplicationKey
     */
    public function setApplicationKeyId(string $applicationKeyId
    ): ApplicationKey {
        $this->applicationKeyId = $applicationKeyId;
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
     * @return ApplicationKey
     */
    public function setBucketId(string $bucketId): ApplicationKey
    {
        $this->bucketId = $bucketId;
        return $this;
    }

    /**
     * @return array
     */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    /**
     * @param array $capabilities
     *
     * @return ApplicationKey
     */
    public function setCapabilities(array $capabilities): ApplicationKey
    {
        $this->capabilities = $capabilities;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExpirationTimestamp(): ?string
    {
        return $this->expirationTimestamp;
    }

    /**
     * @param string|null $expirationTimestamp
     *
     * @return ApplicationKey
     */
    public function setExpirationTimestamp(?string $expirationTimestamp
    ): ApplicationKey {
        $this->expirationTimestamp = $expirationTimestamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return $this->keyName;
    }

    /**
     * @param string $keyName
     *
     * @return ApplicationKey
     */
    public function setKeyName(string $keyName): ApplicationKey
    {
        $this->keyName = $keyName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNamePrefix(): ?string
    {
        return $this->namePrefix;
    }

    /**
     * @param string|null $namePrefix
     *
     * @return ApplicationKey
     */
    public function setNamePrefix(?string $namePrefix): ApplicationKey
    {
        $this->namePrefix = $namePrefix;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return ApplicationKey
     */
    public function setOptions(array $options): ApplicationKey
    {
        $this->options = $options;
        return $this;
    }


}