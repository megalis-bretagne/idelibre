<?php


namespace App\Controller\WebService;


use Symfony\Component\Routing\Annotation\Route;

class LegacyWsController
{

    /**
     * @Route("/seance.json", name="wd_connector")
     *
     */
    public function addSitting()
    {
        dd('ok');
    }


}
