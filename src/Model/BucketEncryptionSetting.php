<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class BucketEncryptionSetting
{

    private ?string $algorithm;

    private ?string $mode;

    public function getAlgorithm(): ?string
    {
        return $this->algorithm;
    }

    public function setAlgorithm(string $algorithm = null): BucketEncryptionSetting
    {
        $this->algorithm = $algorithm;
        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(string $mode = null): BucketEncryptionSetting
    {
        $this->mode = $mode;
        return $this;
    }

}