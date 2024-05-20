<?php

namespace App\Service\RecapNotificationMail\Model;

use App\Entity\Structure;
use App\Entity\User;

class EmailRecapData
{
    /**
     * @var array<string>
     */
    private array $generatedRecapContents;

    public function __construct(private User $user, private array $recapSittingInfo, private Structure $structure)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): EmailRecapData
    {
        $this->user = $user;
        return $this;
    }


    /**
     * @return array<RecapSittingInfo>
     */
    public function getRecapSittingInfo(): array
    {
        return $this->recapSittingInfo;
    }

    /**
     * @param array<RecapSittingInfo> $recapSittingInfo
     */
    public function setRecapSittingInfo(array $recapSittingInfo): EmailRecapData
    {
        $this->recapSittingInfo = $recapSittingInfo;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getGeneratedRecapContents(): array
    {
        return $this->generatedRecapContents;
    }

    /**
     * @param array<string> $generatedRecapContents
     */
    public function setGeneratedRecapContent(array $generatedRecapContents): EmailRecapData
    {
        $this->generatedRecapContents = $generatedRecapContents;
        return $this;
    }

    public function getStructure(): Structure
    {
        return $this->structure;
    }

    public function setStructure(Structure $structure): EmailRecapData
    {
        $this->structure = $structure;
        return $this;
    }
}
