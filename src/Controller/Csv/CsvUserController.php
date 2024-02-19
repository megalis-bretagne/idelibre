<?php

namespace App\Controller\Csv;

use App\Form\CsvType;
use App\Service\Csv\CsvUserManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Sidebar(active: ['user-nav'])]
#[Breadcrumb(title: 'Utilisateurs', routeName: 'user_index')]
class CsvUserController extends AbstractController
{
    /**
     * @throws UnavailableStream
     * @throws Exception
     */
    #[Route(path: '/csv/importUsers', name: 'csv_add_users')]
    #[IsGranted('ROLE_MANAGE_USERS')]
    #[Breadcrumb(title: 'Importer des utilisateurs via csv')]
    public function importUsers(Request $request, CsvUserManager $csvUserManager, Session $session): Response
    {
        $form = $this->createForm(CsvType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $csvUserManager->importUsers($form->get('csv')->getData(), $this->getUser()->getStructure());
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

    #[Route(path: '/csv/userErrors', name: 'user_csv_error')]
    #[IsGranted('ROLE_MANAGE_USERS')]
    #[Breadcrumb(title: "Erreurs lors de l'import")]
    public function csvUsersError(Session $session): Response
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
