<?php

namespace App\Serializer;


use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JsonExeptionSerializer implements NormalizerInterface
{
    public function normalize($exception, string $format = null, array $context = [])
    {

        dd('ooo');
        return [
            'content' => 'This is my custom problem normalizer.',
            'exception' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getStatusCode(),
            ],
        ];
    }

    public function supportsNormalization($data, string $format = null)
    {

        dump("amlsdksdfl");
        return $data instanceof FlattenException;
    }
}
