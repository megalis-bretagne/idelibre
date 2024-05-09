<?php

namespace App\Controller\Easy;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EasyLoginController extends AbstractController
{

    #[Route(path: '/easy/magic-link', name: 'magic_link')]
    public function magicLink()
    {
        // symfony needs a method to be able to generate the route
    }


    #[Route(path: '/easy/magic-link/unauthorized', name: 'unauthorized_magic_link')]
    public function onUnauthorizedMagicLink(): Response
    {
        return $this->render('easy/magic_link/unauthorized.html.twig');
    }


    #[Route(path: '/easy/magic-link/invalid', name: 'invalid_magic_link')]
    public function onInvalidMagicLink(): Response
    {
        return $this->render('easy/magic_link/invalid.html.twig');
    }



}