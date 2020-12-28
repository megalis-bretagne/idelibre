<?php

namespace App\Controller;

use App\Sidebar\Annotation\Sidebar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Sidebar(active={"sitting","salut"})
 * @Sidebar(active={"titi"})
 */
class CountryController extends AbstractController
{
    /**
     * @Route("/country", name="structure")
     *
     * @Sidebar(reset=true)
     * @Sidebar(active={"type"})
     * @Sidebar(active={"toto"})
     */
    public function getAll(): Response
    {
        return $this->render('user/index.html.twig');
    }
}
