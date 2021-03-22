<?php

namespace App\Service\Email;

class Attachment
{
    private string $fileName;
    private string $path;
    private string $contentType;

    public function __construct(string $fileName, string $path, string $contentType = 'application/pdf')
    {
        $this->fileName = $fileName;
        $this->path = $path;
        $this->contentType = $contentType;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }
}
