<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Jwt\JwtInvalidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JwtController extends AbstractController
{
    #[Route('/jwt/invalidate/{id}', name: 'jwt_invalidate', methods: ['POST'])]
    #[IsGranted(data: 'MANAGE_USERS', subject: 'user')]
    public function invalidateBeforeNow(User $user, JwtInvalidator $jwtInvalidator): Response
    {
        $jwtInvalidator->invalidate($user);

        $this->addFlash('success', 'Toutes les connexions actives de l\'élu ont été supprimées');

        return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
    }
}
