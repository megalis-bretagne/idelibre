<?php

namespace App\Util;

use Exception;

class TokenUtil
{
    /**
     * @throws Exception
     */
    public static function genToken():string
    {
        return bin2hex(random_bytes(60));
    }

}
