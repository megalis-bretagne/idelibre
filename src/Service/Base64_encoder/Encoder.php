<?php

namespace App\Service\Base64_encoder;

class Encoder
{
    public function encodeImages(string $content): string
    {
        return preg_replace_callback(
            '/<img src="([^"]+)"[^>]+>/',
            $this->imgEncode($content),
            $content
        );
    }

    private function imgEncode(string $content): callable
    {
        return function ($matches) {
            foreach($matches as $match) {
                $pattern = '/https:\/\/\S+\.(jpg|png)/';
                preg_match($pattern, $match, $imgPath);
                $extension = pathinfo($imgPath[0], PATHINFO_EXTENSION);
                $encodedImage = base64_encode($imgPath[0]);
                return str_replace($imgPath[0], 'data:image/' . $extension .';base64,' . $encodedImage, $match);
            }
        };
    }

}
