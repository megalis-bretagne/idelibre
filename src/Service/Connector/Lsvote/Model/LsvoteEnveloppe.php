<?php

namespace App\Service\Connector\Lsvote\Model;

class LsvoteEnveloppe
{


    private LsvoteSitting $sitting;

    /** @var array<LsvoteProject>  */
    private array $projects;

    /** @var array<LsvoteVoter> */
    private array $voters;


    public function getSitting(): LsvoteSitting
    {
        return $this->sitting;
    }

    public function setSitting(LsvoteSitting $sitting): LsvoteEnveloppe
    {
        $this->sitting = $sitting;
        return $this;
    }

    public function getProjects(): array
    {
        return $this->projects;
    }

    public function setProjects(array $projects): LsvoteEnveloppe
    {
        $this->projects = $projects;
        return $this;
    }

    public function getVoters(): array
    {
        return $this->voters;
    }

    public function setVoters(array $voters): LsvoteEnveloppe
    {
        $this->voters = $voters;
        return $this;
    }

}