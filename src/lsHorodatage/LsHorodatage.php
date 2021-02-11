<?php


namespace App\lsHorodatage;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Libriciel\ComelusApiWrapper\ComelusException;
use Psr\Http\Message\StreamInterface;

class LsHorodatage
{

    private Client $client;
    private string $url;


    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @throws LsHorodatageException
     */
    public function setUrl(string $url)
    {
        $httpRegex = "~^((https?)://).+$~";
        if (!preg_match($httpRegex, $url)) {
            throw new LsHorodatageException("malformed url : missing http|https", 400);
        }
        $this->url = $url;
    }


    /**
     * @throws LsHorodatageException
     */
    private function checkInitialized()
    {
        if (!$this->url) {
            throw new LsHorodatageException("url must be set");
        }
    }

    /**
     * @throws LsHorodatageException
     */
    public function ping(): string
    {
        $this->checkInitialized();

        try {
            $response = $this->client->request('GET', $this->url . '/ping');
        } catch (GuzzleException $e) {
            throw new LsHorodatageException("erreur d'access : " . $e->getMessage());
        }
        return $response->getBody()->getContents();
    }


    /**
     * @throws LsHorodatageException
     */
    public function createTimestampToken(string $filePath): StreamInterface
    {
        $this->checkInitialized();

        try {
            $response = $this->client->request('POST', $this->url . '/timestamp/create',
                [
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => fopen($filePath, 'r')
                        ],
                    ]
                ]);
        } catch (GuzzleException $e) {
            throw new LsHorodatageException('timestamping error : ' . $e->getMessage());
        }
        return $response->getBody();
    }


    /**
     * @throws LsHorodatageException
     */
    public function readTimestampToken(string $filePath): string
    {
        $this->checkInitialized();

        try {
            $response = $this->client->request('POST', $this->url . '/timestamp/read',
                [
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => fopen($filePath, 'r')
                        ],
                    ]
                ]);


        } catch (GuzzleException $e) {
            throw new LsHorodatageException('reading error : ' . $e->getMessage());
        }

        return $response->getBody()->getContents();
    }


    /**
     * @param string $filePath
     * @throws LsHorodatageException
     */
    public function verifyTimestampToken(string $filePath, string $tokenPath): bool
    {
        $this->checkInitialized();
        try {
            $response = $this->client->request('POST', $this->url . '/timestamp/verify',
                [
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => fopen($filePath, 'r')
                        ],
                        [
                            'name' => 'tokenReply',
                            'contents' => fopen($tokenPath, 'r')
                        ]
                    ]
                ]);
        } catch (GuzzleException $e) {
            throw new LsHorodatageException('verification error : ' . $e->getMessage());
        }

        $content = json_decode($response->getBody()->getContents(), true);

        return $content['isValid'];
    }
}
