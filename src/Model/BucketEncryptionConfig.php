<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class BucketEncryptionConfig
{

    private bool $isClientAuthorizedToRead;

    private BucketEncryptionSetting $value;

    public function isClientAuthorizedToRead(): bool
    {
        return $this->isClientAuthorizedToRead;
    }

    public function setIsClientAuthorizedToRead(bool $isClientAuthorizedToRead
    ): BucketEncryptionConfig {
        $this->isClientAuthorizedToRead = $isClientAuthorizedToRead;
        return $this;
    }

    public function getValue(): BucketEncryptionSetting
    {
        return $this->value;
    }

    public function setValue(BucketEncryptionSetting $value
    ): BucketEncryptionConfig {
        $this->value = $value;
        return $this;
    }

}