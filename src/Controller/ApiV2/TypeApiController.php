<?php

namespace App\Controller\ApiV2;

use App\Entity\Structure;
use App\Entity\Type;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[Route('/api/v2/structure/{structureId}/types')]
#[ParamConverter('structure', class: Structure::class, options: ['id' => 'structureId'])]
class TypeApiController extends AbstractController
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/', name: 'get_all_types', methods: ['GET'])]
    public function getAll(
        Structure $structure,
        TypeRepository $typeRepository
    ): JsonResponse
    {
        $types = $typeRepository->findByStructure($structure)->getQuery()->getResult();

        return $this->json($types, context: ['groups' => 'type:read']);
    }

    #[Route('/{id}', name: 'get_type', methods: ['GET'])]
    public function getById(
        Structure $structure,
        Type $type
    ): JsonResponse
    {
        return $this->json($type, context: ['groups' => ['type:detail', 'type:read']]);
    }

    /**
     * body {"name":"string","isSms":bool,"isComelus":bool,"reminder":{"duration":int,"isActive":bool}}.
     */
    #[Route('', name: 'add_type', methods: ['POST'])]
    public function add(Structure $structure, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var Type $type */
        $type = $this->denormalizer->denormalize($data, Type::class, context:['groups' => ['type:write']]);

        $type->setStructure($structure);

        $this->em->persist($type);
        $this->em->flush();

        return $this->json($type, status: 201, context: ['groups' => ['type:detail', 'type:read']]);
    }

    /**
     * body {"name":"string","isSms":bool,"isComelus":bool,"reminder":{"duration":int,"isActive":bool}}.
     */
    #[Route('/{id}', name: 'edit_type', methods: ['PUT'])]
    public function edit(Structure $structure, Type $type, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $context = ['object_to_populate' => $type, 'groups' => ['type:write']];

        /** @var Type $type */
        $updatedType = $this->denormalizer->denormalize($data, Type::class, context: $context);

        $this->em->persist($updatedType);
        $this->em->flush();

        return $this->json($type, context: ['groups' => ['type:detail', 'type:read']]);
    }

    #[Route('/{id}', name: 'delete_type', methods: ['DELETE'])]
    public function delete(Structure $structure, Type $type): JsonResponse
    {
        $this->em->remove($type);
        $this->em->flush();

        return $this->json(null, status: 204);
    }

    /*
    $structure->getName();
         dd($structure);


     $format = 'application/json';
     $type = Type::class;
     $data = ['name' => ''];

     $typeObj = $typeRepository->findAll()[0];
     dump($typeObj);
     $context = ['object_to_populate' => $typeObj, 'groups' => ['type:read']];

     $updatedType = $this->denormalizer->denormalize($data, $type, $format, $context);

     $res = $validator->validate(($updatedType));

     if ($res) {
         throw new BadRequestHttpException('Message');
     }

     $em->persist($updatedType);
     $em->flush();

     dd($updatedType);
     dump($denormalizer);
     dd('ok');

 }*/
}
