<?php

namespace App\Util;

use App\Entity\AttendanceToken;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class AttendanceTokenUtil
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws Exception
     */
    public function prepareToken(DateTimeInterface $expiredAt): AttendanceToken
    {
        $attendanceToken = new AttendanceToken();
        $attendanceToken->setExpiredAt($expiredAt);

        $this->entityManager->persist($attendanceToken);

        return $attendanceToken;
    }
}
