<?php

namespace App\Controller\Easy;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class EasyLoginController extends AbstractController
{

    #[Route(path: '/easy/magic-link', name: 'magic_link')]
    public function magicLink()
    {
        // symfony needs a method to be able to generate the route
    }



}