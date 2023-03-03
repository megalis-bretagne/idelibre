<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Password\LegacyPassword;
use App\Service\User\ImpersonateStructure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

#[AsEventListener(AuthenticationFailureHandlerInterface::class, method: 'check')]
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    public function __construct(
        private readonly RouterInterface             $router,
        private readonly ImpersonateStructure        $impersonateStructure,
        private readonly Security                    $security,
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LegacyPassword              $legacyPassword,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username');
        $plainPassword = $request->request->get('password');

        if ($this->checkCredentialsAndUpdateIfLegacy($username, $plainPassword)) {
            return new SelfValidatingPassport(new UserBadge($username));
        }

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password')),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
            ]
        );
    }

    private function checkCredentialsAndUpdateIfLegacy(string $username, string $plainPassword): bool
    {
        $user = $this->userRepository->findOneBy([
            'username' => $username,
            'isActive' => true,
        ]);

        if (!$user || $this->isInUnActiveStructure($user)) {
            return false;
        }

        if ($this->passwordHasher->isPasswordValid($user, $plainPassword)) {
            return true;
        }

        return $this->legacyPassword->checkAndUpdateCredentials($user, $plainPassword);
    }

    private function isInUnActiveStructure(User $user): bool
    {
        if (!$user->getStructure()) {
            return false;
        }

        if (!$user->getStructure()->getIsActive()) {
            return $user->getRole()->getIsInStructureRole();
        }

        return false;
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
        $errorMsg = 'erreur d\'identification';
        if ($exception instanceof TooManyLoginAttemptsAuthenticationException) {
            $errorMsg = "Vous avez renseigné 5 mots de passe incorrects consécutivement.
             Vous devez attendre 3 minutes avant de pouvoir essayer à nouveau.
             Si vous avez perdu votre mot de passe merci de cliquez sur mot de passe oublié";
        }

        $request->getSession()->getFlashBag()->add('error', $errorMsg);

        return new RedirectResponse($this->router->generate('app_login'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('app_login');
    }

    public function supports(Request $request): bool
    {
        return 'app_login' === $request->attributes->get('_route') && $request->isMethod('POST');
    }
}
