<?php

namespace App\Service\ServiceInfo;


class ServiceInfo
{
    public function getPhpConfiguration(): array
    {
        return [
            'memory_limit' => ini_get('memory_limit'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_file_uploads' => ini_get('max_file_uploads'),
        ];
    }
}
