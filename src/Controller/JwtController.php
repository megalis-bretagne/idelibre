<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\Http403Exception;
use App\Service\Jwt\JwtInvalidator;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JwtController extends AbstractController
{
    #[Route('/jwt/invalidate/{id}', name: 'jwt_invalidate', methods: ['POST'])]
    #[IsGranted( 'MANAGE_USERS', subject: 'user')]
    public function invalidateBeforeNow(User $user, JwtInvalidator $jwtInvalidator): Response
    {
        $jwtInvalidator->invalidate($user);

        $this->addFlash('success', 'Toutes les connexions actives de consultation des séances de l\'utilisateur ont été supprimées');

        return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
    }

    #[Route('/jwt/invalidateNodejs/{id}', name: 'jwt_invalidate_nodejs', methods: ['POST'])]
    public function invalidateBeforeNowFromNodejs(User $user, JwtInvalidator $jwtInvalidator, Request $request, ParameterBagInterface $bag): JsonResponse
    {
        if ($request->get('passPhrase') !== $bag->get('nodejs_passphrase')) {
            throw new Http403Exception('Not authorized');
        }
        $jwtInvalidator->invalidate($user);

        return $this->json(['success' => true]);
    }
}
