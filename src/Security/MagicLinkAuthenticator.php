<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Security\Password\LegacyPassword;
use App\Service\User\ImpersonateStructure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;


class MagicLinkAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly RouterInterface $router,
    )
    {
    }

    public function supports(Request $request): bool
    {
        return 'magic_link' === $request->attributes->get('_route') && $request->isMethod('GET');
    }

    public function authenticate(Request $request): Passport
    {

        $userId = 'elu1@marjolsv'; // elu1@marjolsv
        $sittingId = '8b6b84e2-a10b-42d1-b71b-88f81ec62c2c';

        $request->getSession()->set('authorizedSittingId', $sittingId);

        return new SelfValidatingPassport(new UserBadge($userId));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
       $sittingId = $request->getSession()->get('authorizedSittingId');
        return new RedirectResponse($this->router->generate('easy_odj_ar', ['id' => $sittingId]));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new Http401Exception("Erreur d'authententification");
    }


}
