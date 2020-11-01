<?php


namespace App\Message;


class UpdatedSitting
{

    private string $sittingId;

    public function __construct(string $sittingId)
    {
        $this->sittingId = $sittingId;
    }


    public function getSittingId(): string
    {
        return $this->sittingId;
    }



}
