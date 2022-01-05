<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class FileRetentionPolicy
{
    private string $mode;

    private FileRetentionPeriod $period;
}