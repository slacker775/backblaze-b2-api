<?php declare(strict_types=1);

namespace Backblaze\B2\Model;

class ListFilesResponse
{

    private array $files;

    private ?string $nextFileName;

    private ?string $nextFileId;

    public function __construct()
    {
        $this->files = [];
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function addFile(File $file): ListFilesResponse
    {
        $this->files[] = $file;
        return $this;
    }

    public function setFiles(array $files): ListFilesResponse
    {
        $this->files = $files;
        return $this;
    }

    public function removeFile(File $file): ListFilesResponse
    {
        return $this;
    }

    public function getNextFileName(): ?string
    {
        return $this->nextFileName;
    }

    public function setNextFileName(?string $nextFileName): ListFilesResponse
    {
        $this->nextFileName = $nextFileName;
        return $this;
    }

    public function getNextFileId(): ?string
    {
        return $this->nextFileId;
    }

    public function setNextFileId(?string $nextFileId): ListFilesResponse
    {
        $this->nextFileId = $nextFileId;
        return $this;
    }

}