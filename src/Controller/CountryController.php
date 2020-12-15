<?php

namespace App\Controller;

use App\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Model(
 *      namespace="TOTO",
 *      version=2,
 *      types={"api","yml"}
 * )
 */
class CountryController extends AbstractController
{
    /**
     * @Route("/country", name="country_index")
     * @Model(
     *     namespace="App\Model\Country",
     *     version=1,
     *     types={"json","xml"}
     * )
     */
    public function getAll(): Response
    {
        return $this->render('country/check.html.twig');
    }
}
