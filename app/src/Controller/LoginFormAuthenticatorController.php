<?php

namespace App\Controller;

use App\Service\Log\LogUserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginFormAuthenticatorController extends AbstractController
{
    public function __construct(LogUserService $log, Security $security)
    {
        $this->log = $log;
        $this->security = $security;
    }

    #[Route(path: '/login', name: 'app_login_base')]
    public function login_base(): Response
    {
        return $this->redirectToRoute('app_login');
    }
    
    #[Route(path: '/{_locale}/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if (!in_array($request->getLocale(), $this->getParameter('app')['admin_locales']))
        {
            return $this->redirectToRoute('app_login_base');
        }

        if ($this->getUser()) {
            if ($this->security->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin');
            } else {
                return $this->redirectToRoute('missing_userforward');
            }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $registration_active = isset($this->getParameter('app')['easyadmin']['registration_active']) ?? false;
        $password_reset_active = isset($this->getParameter('app')['easyadmin']['passwort_reset_active']) ?? false;
        
        return $this->render('view/auth/login/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error,

            'csrf_token_intention' => 'authenticate',
            'registration_active' => $registration_active,
            'password_reset_active' => $password_reset_active,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
