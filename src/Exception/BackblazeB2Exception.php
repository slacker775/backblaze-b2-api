<?php declare(strict_types=1);

namespace Backblaze\B2\Exception;

class BackblazeB2Exception extends \Exception
{
    private int $status;
}