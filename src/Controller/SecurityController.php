<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\Structure;
use App\Form\UserPasswordType;
use App\Security\Http400Exception;
use App\Security\Password\ResetPassword;
use App\Security\Password\TimeoutException;
use App\Service\User\ImpersonateStructure;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/', name: 'app_entrypoint')]
    public function entryPoint(Security $security): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if (Role::NAME_ROLE_ACTOR === $this->getUser()->getRole()->getName()) {
            return $this->render('security/noActors.html.twig');
        }
        if ($this->isGranted('ROLE_MANAGE_STRUCTURES')) {
            return $this->redirectToRoute('structure_index');
        }

        return $this->redirectToRoute('sitting_index', ['status' => 'active']);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('error', 'erreur d\'identification');
        }
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/Login_ls.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout()
    {
    }

    #[Route(path: '/security/impersonate/{id}', name: 'security_impersonate')]
    #[IsGranted(data: 'MY_GROUP', subject: 'structure')]
    public function impersonateAs(Structure $structure, ImpersonateStructure $impersonateStructure): Response
    {
        $impersonateStructure->logInStructure($structure);
        $this->addFlash('success', 'Vous êtes connecté dans la structure ' . $structure->getName());

        return $this->redirectToRoute('structure_index');
    }

    #[Route(path: '/security/impersonateExit', name: 'security_impersonate_exit')]
    #[IsGranted(data: 'ROLE_MANAGE_STRUCTURES')]
    public function impersonateExit(ImpersonateStructure $impersonateStructure): Response
    {
        $impersonateStructure->logoutStructure();
        $this->addFlash('success', 'Vous n\'êtes plus connecté dans une structure');

        return $this->redirectToRoute('structure_index');
    }

    /**
     * @throws EntityNotFoundException
     */
    #[Route(path: '/forget', name: 'app_forget')]
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
        if(empty($data["username"])) {
            return $this->json(["message" => "username is required"], 400);
        }

        try {
            $resetPassword->reset($data["username"]);
        } catch (EntityNotFoundException $e) {
            $logger->info('this username does not exist : ' . $data["username"]);
        }

        return $this->json(["message" => "email sent if username exists"]);
    }


    /**
     * @throws Exception
     */
    #[Route(path: '/reset/{token}', name: 'app_reset')]
    public function resetPassword(string $token, ResetPassword $resetPassword, Request $request): Response
    {
        try {
            $user = $resetPassword->getUserFromToken($token);
        } catch (TimeoutException $e) {
            throw new TimeoutException('expired TOKEN', 400);
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException('this token does not exist');
        }
        $form = $this->createForm(UserPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $resetPassword->setNewPassword($user, $form->get('plainPassword')->getData());
            $this->addFlash('success', 'Modifiée avec succès');

            return $this->redirectToRoute('app_login');
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
}
