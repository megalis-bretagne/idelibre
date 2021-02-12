<?php

namespace App\Service\Timestamp;

use Libriciel\LshorodatageApiWrapper\LsHorodatage;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;

class LshorodatageFactory
{
    private LsHorodatage $lsHorodatage;
    private FakeLshorodatage $fakeLshorodatage;

    public function __construct(LsHorodatage $lsHorodatage, FakeLshorodatage $fakeLshorodatage)
    {
        $this->lsHorodatage = $lsHorodatage;
        $this->fakeLshorodatage = $fakeLshorodatage;
    }

    public function chooseImplementation(): LshorodatageInterface
    {
        if ('test' === getenv('APP_ENV')) {
            return $this->fakeLshorodatage;
        }

        return $this->lsHorodatage;
    }
}
