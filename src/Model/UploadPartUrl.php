<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class UploadPartUrl
{

    private string $fileId;

    private string $uploadUrl;

    private string $authorizationToken;

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function setFileId(string $fileId): UploadPartUrl
    {
        $this->fileId = $fileId;
        return $this;
    }

    public function getUploadUrl(): string
    {
        return $this->uploadUrl;
    }

    public function setUploadUrl(string $uploadUrl): UploadPartUrl
    {
        $this->uploadUrl = $uploadUrl;
        return $this;
    }

    public function getAuthorizationToken(): string
    {
        return $this->authorizationToken;
    }

    public function setAuthorizationToken(string $authorizationToken
    ): UploadPartUrl {
        $this->authorizationToken = $authorizationToken;
        return $this;
    }

}