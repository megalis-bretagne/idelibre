<?php

namespace App\Controller;

use App\Annotation\Sidebar;
use App\Form\CsvType;
use App\Service\Csv\CsvManager;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Utilisateurs", routeName="user_index")
 * @Sidebar(active={"user-nav"})
 */
class CsvController extends AbstractController
{
    /**
     * @Route("/csv/importUsers", name="csv_add_users")
     * @IsGranted("ROLE_MANAGE_USERS")
     * @Breadcrumb("Importer des utilisateurs via csv")
     */
    public function importUsers(Request $request, CsvManager $csvManager, Session $session): Response
    {
        $form = $this->createForm(CsvType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $csvManager->importUsers($form->get('csv')->getData(), $this->getUser()->getStructure());
            if (empty($errors)) {
                $this->addFlash('success', 'Fichier csv importé avec succès');

                return $this->redirectToRoute('user_index');
            }
            $session->set('errors_csv', $errors);

            return $this->redirectToRoute('user_csv_error');
        }

        return $this->render('csv/importCsv.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/csv/errors", name="user_csv_error")
     * @IsGranted("ROLE_MANAGE_USERS")
     * @Breadcrumb("Erreur lors de l'import")
     */
    public function csvError(Session $session): Response
    {
        $errors = $session->get('errors_csv');

        if (empty($errors)) {
            return $this->redirectToRoute('user_index');
        }

        $session->remove('errors_csv');

        return $this->render('csv/importCsvErrors.html.twig', [
            'errors' => $errors,
        ]);
    }
}
