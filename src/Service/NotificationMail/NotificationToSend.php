<?php

namespace App\Service\NotificationMail;

use App\Entity\Structure;
use App\Entity\User;

class NotificationToSend
{

    private string $content;

    public function __construct(private User $user, private array $notificationsData, private Structure $structure)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): NotificationToSend
    {
        $this->user = $user;
        return $this;
    }


    /**
     * @return array<NotificationData>
     */
    public function getNotificationsData(): array
    {
        return $this->notificationsData;
    }

    /**
     * @param array<NotificationData> $notificationsData
     */
    public function setNotificationsData(array $notificationsData): NotificationToSend
    {
        $this->notificationsData = $notificationsData;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): NotificationToSend
    {
        $this->content = $content;
        return $this;
    }

    public function getStructure(): Structure
    {
        return $this->structure;
    }

    public function setStructure(Structure $structure): NotificationToSend
    {
        $this->structure = $structure;
        return $this;
    }

}