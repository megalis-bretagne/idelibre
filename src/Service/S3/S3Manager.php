<?php

namespace App\Service\S3;

use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class S3Manager
{
    private string $parameterBucket;

    public function __construct(
        private readonly S3Client $s3Client,
        private readonly LoggerInterface $logger,
        ParameterBagInterface $bag,
    ) {
        $this->parameterBucket = $bag->get('s3.bucket');
    }

    private function getBucketName(?string $bucketName = null)
    {
        if (empty($bucketName)) {
            return $this->parameterBucket;
        }

        return $bucketName;
    }

    /**
     * @param $name
     *
     * @throws ObjectStorageException
     */
    public function createBucket($name): bool
    {
        try {
            $this->s3Client->createBucket([
                'Bucket' => $name,
            ]);
        } catch (AwsException $e) {
            $this->logger->error($e->getMessage());
            throw new ObjectStorageException($e->getMessage());
        }

        return true;
    }

    /**
     * @throws ObjectStorageException
     */
    public function listBuckets(): iterable
    {
        try {
            $bucketList = $this->s3Client->listBuckets();
        } catch (AwsException $e) {
            $this->logger->error($e->getMessage());
            throw new ObjectStorageException($e->getMessage());
        }

        return $bucketList;
    }

//    /**
//     * @param $name
//     * @throws ObjectStorageException
//     */
//    public function deleteBucket($name): bool
//    {
//        try {
//            $this->s3Client->deleteBucket([
//                "Bucket" => $name
//            ]);
//        } catch (AwsException $e) {
//            $this->logger->error($e->getMessage());
//            throw new ObjectStorageException($e->getMessage());
//        }
//
//        return true;
//    }

    /**
     * @throws ObjectStorageException
     */
    public function listObjects(?string $bucketName = null): iterable
    {
        $bucketName = $this->getBucketName($bucketName);

        try {
            return $this->s3Client->listObjects([
                'Bucket' => $bucketName,
            ]);
        } catch (S3Exception $e) {
            $this->logger->error($e->getMessage());
            throw new ObjectStorageException($e->getMessage());
        }
    }

    /**
     * @param string $bucketName
     *                           ! Ne verifie pas si l'objecct n'existe pas. renvoi true quand meme
     *
     * @throws ObjectStorageException
     */
    public function deleteObject(string $keyName, ?string $bucketName = null): bool
    {
        $bucketName = $this->getBucketName($bucketName);

        try {
            $this->s3Client->deleteObject([
                'Bucket' => $bucketName,
                'Key' => $keyName,
            ]);
        } catch (AwsException $e) {
            $this->logger->error($e->getMessage());
            throw new ObjectStorageException($e->getMessage());
        }

        return true;
    }

    /**
     * @param string $bucketName
     *                           ! Ne garanti pas que les objets passer en parametre existe renvoi true mem si n'existe pas
     *
     * @throws ObjectStorageException
     */
    public function deleteObjects(array $keyNames, ?string $bucketName = null): bool
    {
        $bucketName = $this->getBucketName($bucketName);

        try {
            $this->s3Client->deleteObjects([
                'Bucket' => $bucketName,
                'Delete' => [
                    'Objects' => array_map(function ($key) {
                        return ['Key' => $key];
                    }, $keyNames),
                ],
            ]);
        } catch (AwsException $e) {
            $this->logger->error($e->getMessage());
            throw new ObjectStorageException($e->getMessage());
        }

        return true;
    }

//    /**
//     * @throws ObjectStorageException
//     */
//    public function deleteObjectsWithPrefix(string $prefix, ?string $bucketName = null)
//    {
//        $bucketName = $this->getBucketName($bucketName);
//
//        try {
//            $this->s3Client->deleteMatchingObjects($bucketName, $prefix);
//        } catch (Exception $e) {
//            $this->logger->error($e->getMessage());
//            throw new ObjectStorageException($e->getMessage());
//        }
//    }

    /**
     * @throws ObjectStorageException
     */
    public function addObject(string $filePath, string $key, ?string $bucketName = null, array $metadata = []): bool
    {
        $bucketName = $this->getBucketName($bucketName);

        try {
            $this->s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => $key,
                'Body' => fopen($filePath, 'r'),
                //'SourceFile' => $filePath,
                'Metadata' => $metadata,
                'ACL' => 'private',
            ]);
        } catch (S3Exception $e) {
            $this->logger->error($e->getMessage());
            throw new ObjectStorageException($e->getMessage());
        }

        return true;
    }

    public function getObject(string $filePath, ?string $bucketName = null)
    {
        $bucketName = $this->getBucketName($bucketName);

        try {
            $file = $this->s3Client->getObject([
                'Bucket' => $bucketName,
                'Key' => $filePath,
            ]);
        } catch (S3Exception $e) {
            $this->logger->error($e->getMessage());
            throw new ObjectStorageException($e->getMessage());
        }

        return $file;
    }

    /**
     * @param string $bucketName
     *                           ttl example : '+10 minutes'
     */
    public function generatePresignedLink(string $fileKey, string $fileName, string $ttl, ?string $bucketName = null): string
    {
        $bucketName = $this->getBucketName($bucketName);
        $normalizeFileName = $this->normalizeString($fileName);

        $cmd = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $bucketName,
            'Key' => $fileKey,
            'ResponseContentDisposition' => "attachment; filename=$normalizeFileName",
        ]);

        $request = $this->s3Client->createPresignedRequest($cmd, $ttl);

        return (string) $request->getUri();
    }

    private function normalizeString($string = '')
    {
        $clean_name = strtr($string, ['Š' => 'S', 'Ž' => 'Z', 'š' => 's', 'ž' => 'z', 'Ÿ' => 'Y', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ÿ' => 'y']);
        $clean_name = strtr($clean_name, ['Þ' => 'TH', 'þ' => 'th', 'Ð' => 'DH', 'ð' => 'dh', 'ß' => 'ss', 'Œ' => 'OE', 'œ' => 'oe', 'Æ' => 'AE', 'æ' => 'ae', 'µ' => 'u']);
        $clean_name = preg_replace(['/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'], ['_', '.', ''], $clean_name);

        return strtolower($clean_name);
    }
}
