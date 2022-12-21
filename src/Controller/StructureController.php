<?php

namespace App\Controller;

use App\Entity\Structure;
use App\Form\SearchType;
use App\Form\StructureInformationType;
use App\Form\StructureType;
use App\Repository\StructureRepository;
use App\Service\RoleTrait;
use App\Service\S3\S3Manager;
use App\Service\Structure\StructureCreator;
use App\Service\Structure\StructureManager;
use App\Service\ValidationTrait;
use App\Sidebar\Annotation\Sidebar;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Sidebar(active: ['platform-nav', 'structure-nav'])]
class StructureController extends AbstractController
{
    use ValidationTrait;
    use RoleTrait;

    #[Route(path: '/structure/checks3', name: 'cs3')]
    public function checkS3(S3Manager $s3Manager): Response
    {
//        $objects = $s3Manager->listObjects();
//
//        foreach ($objects as $key => $object) {
//            if ('Contents' === $key) {
//                foreach ($object as $obj) {
//                    $s3Manager->deleteObjects([
//                        $obj['Key'],
//                    ]);
//                }
//            }
//        }
//
//        dd($s3Manager->listObjects());

        dd($s3Manager->listObjects());
        //        dd($s3Manager->deleteObjects([
        //            '1b9bf70e-6e5a-4950-a31e-60a644f2dd28/2022/06/cv_theo_guillon_62b1cee347602.pdf',
        //        ]));
        //        dd($s3Manager->getObjects($bag->get('s3.bucket')));
    }

    #[Route(path: '/structure', name: 'structure_index')]
    #[IsGranted(data: 'ROLE_MANAGE_STRUCTURES')]
    public function index(StructureRepository $structureRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $formSearch = $this->createForm(SearchType::class);
        if ($this->isSuperAdmin($this->getUser())) {
            $structureQueryList = $structureRepository->findAllQueryBuilder($request->query->get('search'));
        } else {
            $structureQueryList = $structureRepository->findByGroupQueryBuilder($this->getUser()->getGroup(), $request->query->get('search'));
        }
        $structures = $paginator->paginate(
            $structureQueryList,
            $request->query->getInt('page', 1),
            $this->getParameter('limit_line_table'),
            [
                'defaultSortFieldName' => ['s.name'],
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('structure/index.html.twig', [
            'structures' => $structures,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search') ?? '',
            'isStructureCreator' => $this->isSuperAdmin($this->getUser()) || $this->getUser()->getGroup()->getIsStructureCreator(),
        ]);
    }

    #[Route(path: '/structure/add', name: 'structure_add')]
    #[IsGranted(data: 'CREATE_STRUCTURE')]

    public function add(Request $request, StructureCreator $structureCreator, ParameterBagInterface $bag): Response
    {
        $form = $this->createForm(StructureType::class, null, [
            'entropyForUser' => $bag->get('minimumEntropyForUserWithRoleHigh'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $structureCreator->create(
                $form->getData(),
                $form->get('user')->getData(),
                $this->getUser()->getGroup()
            );

            if (!empty($errors)) {
                $this->addErrorToForm($form->get('user'), $errors);

                return $this->render('structure/add.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $this->addFlash('success', 'La structure a été créée');

            return $this->redirectToRoute('structure_index');
        }

        return $this->render('structure/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/structure/edit/{id}', name: 'structure_edit')]
    #[IsGranted(data: 'MY_GROUP', subject: 'structure')]

    public function edit(Structure $structure, Request $request, StructureManager $structureManager): Response
    {
        $form = $this->createForm(StructureType::class, $structure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $structureManager->save($form->getData());
            $this->addFlash('success', 'La structure a été modifiée');

            return $this->redirectToRoute('structure_index');
        }

        return $this->render('structure/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/structure/delete/{id}', name: 'structure_delete', methods: ['DELETE'])]
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
    public function delete(Structure $structure, StructureManager $structureManager, Request $request): Response
    {
        $structureManager->delete($structure);
        $this->addFlash('success', 'La structure a bien été supprimée');

        return $this->redirectToRoute('structure_index', [
            'page' => $request->get('page'),
        ]);
    }

    #[Route(path: '/structure/preferences', name: 'structure_preferences')]
    #[IsGranted(data: 'ROLE_STRUCTURE_ADMIN')]
    #[Sidebar(reset: true, active: ['structure-preference-nav'])]

    public function preferences(Request $request, StructureManager $structureManager): Response
    {
        $form = $this->createForm(StructureInformationType::class, $this->getUser()->getStructure());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $structureManager->save($form->getData());
            $this->addFlash('success', 'Les informations de la structure ont été mises à jour');

            return $this->redirectToRoute('app_entrypoint');
        }

        return $this->render('structure/preferences.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
