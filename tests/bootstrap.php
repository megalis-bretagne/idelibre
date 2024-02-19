<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}


$tokenSuffix = getenv('TEST_TOKEN') ? '_' . getenv('TEST_TOKEN') : '';
if (!defined('TMP_TESTDIR')) {
    define('TMP_TESTDIR', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testunit' . $tokenSuffix);
}
