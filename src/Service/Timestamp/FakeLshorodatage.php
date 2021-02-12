<?php


namespace App\Service\Timestamp;


use GuzzleHttp\Psr7\Stream;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;
use Psr\Http\Message\StreamInterface;

class FakeLshorodatage implements LshorodatageInterface
{

    public function setUrl(string $url): void {
        // Do nothing
    }

    public function ping(): string
    {
        return json_encode(['success' => true]);
    }

    public function createTimestampToken(string $filePath): StreamInterface
    {
        $stream = fopen('php://memory','r+');
        fwrite($stream, 'fake horodatage');
        rewind($stream);

        return new Stream($stream);
    }

    public function readTimestampToken(string $filePath): string
    {
        return 'fake decoded content';
    }

    public function verifyTimestampToken(string $filePath, string $tokenPath): bool
    {
        return true;
    }
}
