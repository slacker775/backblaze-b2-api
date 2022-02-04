<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class FileLockConfiguration
{

    private bool $isClientAuthorizedToRead;

    private ?FileRetentionPolicy $value;

    private bool $isFileLockEnabled;

    public function isClientAuthorizedToRead(): bool
    {
        return $this->isClientAuthorizedToRead;
    }

    public function setIsClientAuthorizedToRead(bool $isClientAuthorizedToRead
    ): FileLockConfiguration {
        $this->isClientAuthorizedToRead = $isClientAuthorizedToRead;
        return $this;
    }

    public function getValue(): ?FileRetentionPolicy
    {
        return $this->value;
    }

    public function setValue(?FileRetentionPolicy $value): FileLockConfiguration
    {
        $this->value = $value;
        return $this;
    }

    public function isFileLockEnabled(): bool
    {
        return $this->isFileLockEnabled;
    }

    public function setIsFileLockEnabled(bool $isFileLockEnabled
    ): FileLockConfiguration {
        $this->isFileLockEnabled = $isFileLockEnabled;
        return $this;
    }

}