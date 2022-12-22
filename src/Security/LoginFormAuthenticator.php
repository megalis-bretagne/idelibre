<?php

namespace App\Security;

use App\Service\User\ImpersonateStructure;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly ImpersonateStructure $impersonateStructure,
        private readonly Security $security,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username');

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password')),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
            ]
        );
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('app_login');
    }

    public function supports(Request $request): bool
    {
        return 'app_login' === $request->attributes->get('_route') && $request->isMethod('POST');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        if ($this->security->isGranted('ROLE_MANAGE_STRUCTURES')) {
            $this->impersonateStructure->logoutStructure();
        }

        return new RedirectResponse($this->router->generate('app_entrypoint'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $request->getSession()->getFlashBag()->add('error', 'erreur d\'identification');
        return new RedirectResponse($this->router->generate('app_login'));
    }
}
