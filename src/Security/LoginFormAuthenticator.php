<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Security\Password\LegacyPassword;
use App\Service\User\ImpersonateStructure;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
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

    public function getCredentials(Request $request): array
    {
        $credentials = [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);

        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        return $this->userRepository->findOneBy(['username' => $credentials['username'], 'isActive' => true]);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        /** @var $user PasswordAuthenticatedUserInterface|UserInterface */
        if ($this->passwordHasher->isPasswordValid($user, $credentials['password'])) {
            return true;
        }

        return $this->legacyPassword->checkAndUpdateCredentials($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): Response
    {
        if ($this->security->isGranted('ROLE_MANAGE_STRUCTURES')) {
            $this->impersonateStructure->logoutStructure();
        }

        return new RedirectResponse($this->router->generate('app_entrypoint'));
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    protected function getLoginUrl(): string
    {
        return $this->router->generate('app_login');
    }
}
