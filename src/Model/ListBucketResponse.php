<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class ListBucketResponse
{

    /**
     * @var Bucket[]
     */
    protected array $buckets;

    public function __construct()
    {
        $this->buckets = [];
    }

    /**
     * @return Bucket[]
     */
    public function getBuckets(): array
    {
        return $this->buckets;
    }

    public function addBucket(Bucket $bucket): void
    {
        $this->buckets[] = $bucket;
    }

    public function removeBucket(Bucket $bucket): void
    {
    }
}