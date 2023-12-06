<?php

namespace App\Controller;

use App\Entity\Enum\Role_Name;
use App\Entity\Role;
use App\Entity\Structure;
use App\Form\UserPasswordType;
use App\Security\Password\PasswordChange;
use App\Security\Password\PasswordUpdater;
use App\Security\Password\PasswordUpdaterException;
use App\Security\Password\ResetPassword;
use App\Security\Password\TimeoutException;
use App\Security\UserLoginEntropy;
use App\Service\User\ImpersonateStructure;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/', name: 'app_entrypoint')]
    public function entryPoint(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if (in_array($this->getUser()->getRole()->getName(), [Role_Name::NAME_ROLE_ACTOR, Role_Name::NAME_ROLE_EMPLOYEE, Role_Name::NAME_ROLE_GUEST])) {
            return $this->render('security/noActors.html.twig');
        }

        if ($this->isGranted('ROLE_MANAGE_STRUCTURES')) {
            return $this->redirectToRoute('structure_index');
        }

        if (!$this->getUser()->getStructure()->getIsActive()) {
            $this->addFlash('error', 'La structure à laquelle vous êtes associé est désactivée');

            return $this->redirectToRoute('app_login');
        }

        return $this->redirectToRoute('sitting_index', ['status' => 'active']);
    }

    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('error', 'Erreur d\'identification');
        }
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/Login_ls.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout', methods: ['GET'])]
    public function logout()
    {
        // symfony needs this empty method
    }

    #[Route(path: '/security/impersonate/{id}', name: 'security_impersonate')]
    #[IsGranted('MY_GROUP', subject: 'structure')]
    public function impersonateAs(Structure $structure, ImpersonateStructure $impersonateStructure): Response
    {
        $impersonateStructure->logInStructure($structure);
        $this->addFlash('success', 'Vous êtes connecté dans la structure ' . $structure->getName());

        return $this->redirectToRoute('structure_index');
    }

    #[Route(path: '/security/impersonateExit', name: 'security_impersonate_exit')]
    #[IsGranted('ROLE_MANAGE_STRUCTURES')]
    public function impersonateExit(ImpersonateStructure $impersonateStructure): Response
    {
        $impersonateStructure->logoutStructure();
        $this->addFlash('success', 'Vous n\'êtes plus connecté dans une structure');

        return $this->redirectToRoute('structure_index');
    }

    #[Route(path: '/forget', name: 'app_forget', methods: ['GET', 'POST'])]
    public function forgetPassword(Request $request, ResetPassword $resetPassword, LoggerInterface $logger): Response
    {
        if ($request->isMethod('post')) {
            $username = $request->request->get('username');

            try {
                $resetPassword->reset($username);
            } catch (EntityNotFoundException $e) {
                $logger->info('this username does not exist : ' . $username);
            }
            $this->addFlash('success', 'Un email vous a été envoyé si un compte lui est associé');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forget_ls.html.twig', [
        ]);
    }

    #[Route(path: '/forgetPasswordJson', name: 'app_forget_json', methods: ['POST'])]
    public function forgetPasswordJson(Request $request, ResetPassword $resetPassword, LoggerInterface $logger): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['username'])) {
            return $this->json(['message' => 'username is required'], 400);
        }

        try {
            $resetPassword->reset($data['username']);
        } catch (EntityNotFoundException $e) {
            $logger->info('this username does not exist : ' . $data['username']);
        }

        return $this->json(['message' => 'email sent if username exists']);
    }


    /**
     * @throws Exception
     */
    #[Route(path: '/reset/{token}', name: 'app_reset', methods: ['GET', 'POST'])]
    public function resetPassword(string $token, ResetPassword $resetPassword, Request $request, UserLoginEntropy $userLoginEntropy): Response
    {
        try {
            $user = $resetPassword->getUserFromToken($token);
        } catch (TimeoutException $e) {
            //throw new TimeoutException('expired TOKEN', 498);
            return $this->render('security/expired_token_ls.html.twig');
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException('this token does not exist');
        }

        $form = $this->createForm(UserPasswordType::class, $user, [
            'entropyForUser' => $userLoginEntropy->getEntropy($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = $resetPassword->setNewPassword($user, $form->get('plainPassword')->getData());

            if (true === $success) {
                $this->addFlash('success', 'Modifié avec succès');

                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('error', 'Votre mot de passe n\'est pas assez fort.');
        }

        return $this->render('security/reset_ls.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/srvusers/login', name: 'legacy_login_path')]
    public function legacyLoginPath(): Response
    {
        return $this->redirectToRoute('app_login');
    }

    #[IsGranted('FROM_NODE', subject: 'request')]
    #[Route(path: '/security/changePassword', name: 'node_change_password')]
    public function changePasswordJson(Request $request, DenormalizerInterface $denormalizer, PasswordUpdater $passwordUpdater): JsonResponse
    {
        try {
            /** @var PasswordChange $passwordChange */
            $passwordChange = $denormalizer->denormalize($request->toArray(), PasswordChange::class);
            $passwordUpdater->replace($passwordChange);
        } catch (ExceptionInterface $e) {
            return $this->json(['message' => 'malformedData'], 400);
        } catch (PasswordUpdaterException $e) {
            return $this->json(
                [
                    'message' => $e->getMessage(),
                    'minEntropyValue' => $e->minEntropyValue,
                    'currentEntropyValue' => $e->currentEntropyValue, ],
                400
            );
        }

        return $this->json(['message' => 'success'], 200);
    }
}
