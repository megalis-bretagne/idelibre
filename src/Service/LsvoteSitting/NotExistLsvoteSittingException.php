<?php

namespace App\Service\LsvoteSitting;

use App\Security\Http400Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotExistLsvoteSittingException extends NotFoundHttpException
{
}
