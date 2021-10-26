<?php

namespace App\Service\Email;

class Attachment
{
    public function __construct(
        private string $fileName,
        private string $path,
        private string $contentType = 'application/pdf'
    ) {
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
