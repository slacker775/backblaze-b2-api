<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class FileRetentionPolicy
{

    private ?string $mode;

    private ?FileRetentionPeriod $period;

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): FileRetentionPolicy
    {
        $this->mode = $mode;
        return $this;
    }

    public function getPeriod(): ?FileRetentionPeriod
    {
        return $this->period;
    }

    public function setPeriod(?FileRetentionPeriod $period): FileRetentionPolicy
    {
        $this->period = $period;
        return $this;
    }

}