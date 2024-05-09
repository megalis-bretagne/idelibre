<?php

namespace App\Security;

use App\Service\Jwt\JwtException;
use App\Service\Jwt\JwtManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;


class MagicLinkAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly JwtManager $jwtManager
    )
    {
    }
//https://localhost/easy/magic-link?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhdm90ZSIsInN1YiI6InQuY2hhcmVzdEBtYXJqb2xzdiIsInNpdHRpbmdJZCI6IjhiNmI4NGUyLWExMGItNDJkMS1iNzFiLTg4ZjgxZWM2MmMyYyIsImlhdCI6MTcxNTI2MTczNiwibmJmIjoxNzE1MjYxNzM2LCJleHAiOjE3MzQxNzQwMDB9.PxJOC3RbQOYFw542wZceZoyWL_DOYSVBLbM3x0crNHM
    public function supports(Request $request): bool
    {
        return 'magic_link' === $request->attributes->get('_route') && $request->isMethod('GET');
    }

    public function authenticate(Request $request): Passport
    {

        $token = $request->query->get('token');

        if(!$token) {
            throw new AuthenticationException("Token manquant");
        }

        try {
          $decoded =  $this->jwtManager->decode($token);
        } catch (JwtException $e) {
            throw new AuthenticationException("Token invalide : " . $e->getMessage());
        }

        $username = $decoded['sub'];
        $sittingId = $decoded['sittingId']; // '8b6b84e2-a10b-42d1-b71b-88f81ec62c2c

        $request->getSession()->set('authorizedSittingId', $sittingId);

        return new SelfValidatingPassport(new UserBadge($username));
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
