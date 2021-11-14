<?php

namespace App\Controller\Csv;

use App\Form\CsvType;
use App\Service\Csv\CsvThemeManager;
use App\Service\Csv\CsvUserManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Utilisateurs", routeName="user_index")
 */
#[Sidebar(active: ['theme-nav'])]
class CsvThemeController extends AbstractController
{
    /**
     * @Breadcrumb("Importer des utilisateurs via csv")
     */
    #[Route(path: '/csv/importTheme', name: 'csv_add_themes')]
    #[IsGranted(data: 'ROLE_MANAGE_THEMES')]
    public function importTheme(Request $request, CsvThemeManager $csvThemeManager, Session $session): Response
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
     * @Breadcrumb("Erreurs lors de l'import")
     */
    #[Route(path: '/csv/themeErrors', name: 'theme_csv_error')]
    #[IsGranted(data: 'ROLE_MANAGE_THEMES')]
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
