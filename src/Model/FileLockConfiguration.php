<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class FileLockConfiguration
{

    private bool $isClientAuthorizedToRead;

    private FileRetentionPolicy $value;

    private bool $isFileLockEnabled;
}