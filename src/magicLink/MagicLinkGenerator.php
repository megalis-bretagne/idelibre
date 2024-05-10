<?php

namespace App\magicLink;

use App\Entity\Sitting;
use App\Entity\User;
use App\Service\Jwt\JwtManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class MagicLinkGenerator
{

    public function __construct(private readonly JwtManager $jwtManager, private readonly RouterInterface $router)
    {
    }

    public function generate(User $user, Sitting $sitting): string
    {
        $validBefore = $sitting->getDate()->modify('+1 day');

        $isAuthorizedMagicLink = !in_array($user->getRole(), ['ROLE_STRUCTURE_ADMIN', 'ROLE_SECRETARY']);

        $jwt = $this->jwtManager->generateTokenForUserNameAndSittingId(
            $user->getUsername(),
            $sitting->getId(),
            $isAuthorizedMagicLink,
            $validBefore);

        return $this->router->generate('magic_link', ['token' => $jwt], UrlGeneratorInterface::ABSOLUTE_URL);
    }

}