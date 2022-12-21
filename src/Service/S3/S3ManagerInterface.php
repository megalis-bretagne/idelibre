<?php

namespace App\Service\S3;

interface S3ManagerInterface
{
    public function createBucket($name): bool;

    public function listBuckets(): iterable;

    public function deleteBucket($name): bool;

    public function listObjects(?string $bucketName = null): iterable;

    public function deleteObject(string $keyName, ?string $bucketName = null): bool;

    public function deleteObjects(array $keyNames, ?string $bucketName = null): bool;

    public function deleteObjectsWithPrefix(string $prefix, ?string $bucketName = null);

    public function addObject(string $filePath, string $key, ?string $bucketName = null, array $metadata = []): bool;

    public function generatePresignedLink(string $fileKey, string $fileName, string $ttl, ?string $bucketName = null): string;
}
