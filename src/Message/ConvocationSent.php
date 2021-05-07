<?php

namespace App\Message;

use App\Entity\Convocation;
use App\Entity\Sitting;

class ConvocationSent
{
    /**
     * @var string[]
     */
    private array $convocationIds;
    private string $sittingId;

    /**
     * @param string[] $convocationIds
     */
    public function __construct(array $convocationIds, string $sittingId)
    {
        $this->convocationIds = $convocationIds;
        $this->sittingId = $sittingId;
    }

    /**
     * @return string[]
     */
    public function getConvocationIds(): array
    {
        return $this->convocationIds;
    }

    public function getSittingId(): string
    {
        return $this->sittingId;
    }


}
