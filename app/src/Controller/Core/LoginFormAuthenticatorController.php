<?php

namespace App\Controller\Core;

use App\Service\Log\LogUserService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

class LoginFormAuthenticatorController extends AbstractController
{
    public function __construct(LogUserService $log, Security $security)
    {
        $this->log = $log;
        $this->security = $security;
    }

    #[Route(path: '/authbridge', name: 'app_auth_bridge')]
    public function auth_bridge(): Response
    {
        if ($this->getUser()) {
            if ($this->security->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin');
            } else {
                return $this->redirectToRoute('missing_userforward');
            }
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/login', name: 'app_login_base')]
    public function login_base(): Response
    {
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/{_locale}/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if (!in_array($request->getLocale(), $this->getParameter('app')['admin_locales'])) {
            return $this->redirectToRoute('app_login_base');
        }

        if ($this->getUser()) {
            return $this->redirectToRoute('app_auth_bridge');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $params = $this->getParameter('app')['core'];
        $registrationActive = isset($params['registration_active']) ? $params['registration_active'] : false;
        $passwordResetActive = isset($params['passwort_reset_active']) ? $params['passwort_reset_active'] : false;

        $userIdentifierType = (
            isset($params['login']['identifier']) and
            in_array($params['login']['identifier'], ['email', 'username','both'])
        ) ? $params['login']['identifier'] : 'email';


        return $this->render('view/core/login/login.html.twig', [
            'lastIdentifier' => $lastUsername,
            'error' => $error,

            'csrfTokenIntention' => 'authenticate',
            'registrationActive' => $registrationActive,
            'passwordResetActive' => $passwordResetActive,
            'userIdentifierType' => $userIdentifierType
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }



    #[Route("/authentication/2fa/enable", name:"app_2fa_enable")]
    #[IsGranted("ROLE_USER")]
    public function enable2fa(GoogleAuthenticatorInterface $googleAutInterface, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if (!$user->isGoogleAuthenticatorEnabled()) {
            $user->setGoogleAuthenticatorSecret($googleAutInterface->generateSecret());
            $entityManager->flush();
        }

        return $this->render('view/core/2fa/enable2fa.html.twig');
    }

    #[Route("/authentication/2fa/qr-code", name: "app_2fa_qr_code")]
    #[IsGranted("ROLE_USER")]
    public function displayGoogleAuthenticatorQrCode(GoogleAuthenticatorInterface $googleAutInterface)
    {
        // $qrCode is provided by the endroid/qr-code library. See the docs how to customize the look of the QR code:
        // https://github.com/endroid/qr-code
        $qrCodeContent = $googleAutInterface->getQRContent($this->getUser());
        $result = Builder::create()
            ->data($qrCodeContent)
            ->encoding(new Encoding('UTF-8'))
            ->size(300)
            ->margin(10)
            ->build();

        return new Response($result->getString(), 300, ['Content-Type' => 'image/png']);
    }
}
