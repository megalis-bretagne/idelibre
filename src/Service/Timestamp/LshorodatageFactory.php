<?php

namespace App\Service\Timestamp;

use Libriciel\LshorodatageApiWrapper\LsHorodatage;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;

class LshorodatageFactory
{
    public function __construct(
        private LsHorodatage $lsHorodatage,
        private FakeLshorodatage $fakeLshorodatage
    )
    {
    }

    public function chooseImplementation(): LshorodatageInterface
    {
        if ('test' === getenv('APP_ENV')) {
            return $this->fakeLshorodatage;
        }

        return $this->lsHorodatage;
    }
}
