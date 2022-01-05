<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class ListKeysResponse
{
    private array $keys;

    private ?string $nextApplicationKeyId;

    public function __construct()
    {
        $this->keys = [];
    }

    public function getKeys(): array
    {
        return $this->keys;
    }

    public function setKeys(array $keys): ListKeysResponse
    {
        $this->keys = $keys;
        return $this;
    }

    public function addKey(ApplicationKey $key): ListKeysResponse
    {
        $this->keys[] = $key;
        return $this;
    }

    public function removeKey(ApplicationKey $key): ListKeysResponse
    {
        return $this;
    }

    public function getNextApplicationKeyId(): ?string
    {
        return $this->nextApplicationKeyId;
    }

    public function setNextApplicationKeyId(?string $nextApplicationKeyId
    ): ListKeysResponse {
        $this->nextApplicationKeyId = $nextApplicationKeyId;
        return $this;
    }
}