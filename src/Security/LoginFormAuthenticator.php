<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Password\LegacyPassword;
use App\Service\User\ImpersonateStructure;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    private UserRepository $userRepository;
    private RouterInterface $router;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private UserPasswordHasherInterface $passwordHasher;
    private Security $security;
    private ImpersonateStructure $impersonateStructure;
    private LegacyPassword $legacyPassword;

    public function __construct(
        UserRepository $userRepository,
        RouterInterface $router,
        CsrfTokenManagerInterface $csrfTokenManager,
        ImpersonateStructure $impersonateStructure,
        UserPasswordHasherInterface $passwordHasher,
        Security $security,
        LegacyPassword $legacyPassword
    ) {
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordHasher = $passwordHasher;
        $this->security = $security;
        $this->impersonateStructure = $impersonateStructure;
        $this->legacyPassword = $legacyPassword;
    }

    public function supports(Request $request): bool
    {
        return 'app_login' === $request->attributes->get('_route') && $request->isMethod('POST');
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('app_login');
    }

    public function authenticate(Request $request): PassportInterface
    {
        $username = $request->request->get('username');
        $plainPassword = $request->request->get('password');
        $csrfToken = $request->request->get('_csrf_token');

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        $token = new CsrfToken('authenticate', $csrfToken);

        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        if ($this->checkCredentialsAndUpdateIfLegacy($username, $plainPassword)) {
            return new SelfValidatingPassport(new UserBadge($username));
        }

        throw new CustomUserMessageAuthenticationException('invalid credentials');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): Response
    {
        if ($this->security->isGranted('ROLE_MANAGE_STRUCTURES')) {
            $this->impersonateStructure->logoutStructure();
        }

        return new RedirectResponse($this->router->generate('app_entrypoint'));
    }

    private function checkCredentialsAndUpdateIfLegacy(string $username, string $plainPassword): bool
    {
        $user = $this->userRepository->findOneBy(['username' => $username, 'isActive' => true]);

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
}
