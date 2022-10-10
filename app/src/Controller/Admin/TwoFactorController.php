<?php

namespace App\Controller\Admin;

use App\Service\Log\LogUserService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\ErrorCorrectionLevel;
use App\Form\Core\TwoFactorEnableFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

class TwoFactorController extends AbstractController
{
    public function __construct(LogUserService $log, Security $security)
    {
        $this->log = $log;
        $this->security = $security;
    }


    #[Route("/authentication/2fa/enable", name:"app_2fa_enable")]
    #[IsGranted("ROLE_USER")]
    public function enable2fa(Request $request, GoogleAuthenticatorInterface $googleAutInterface, EntityManagerInterface $entityManager)
    {
        $errorMsg = null;
        $user = $this->getUser();
        if(!$user)
        {
            return $this->redirectToRoute('app_logout');
        }

        $verificationCode = (int) $request->request->get('verificationCode');
        if ($verificationCode)
        {
            if (!$user->isGoogleAuthenticatorEnabled()) {
                if( $googleAutInterface->checkCode($user, $verificationCode))
                {
                    $user->setTwoFactorEnabled(true);
                    $entityManager->flush();
                }else{
                    $errorMsg = "verification code wrong";
                }
            }
        }else{
            if (!$user->isGoogleAuthenticatorEnabled()) {
                $user->setGoogleAuthenticatorSecret($googleAutInterface->generateSecret());
                $user->setTwoFactorEnabled(false);
                $entityManager->flush();
            }
        }

        $isLoggedInUser = ($user->getId() == $this->security->getUser()->getId());
        return $this->render('view/core/2fa/enable2fa.html.twig',[
            'isEnabled' => $user->isTwoFactorEnabled(),
            'isLoggedInUser' => $isLoggedInUser,
            'disableURL' => '/admin?routeName=app_2fa_disable&disable=1',
            'errorMsg' => $errorMsg
        ]);
    }

    #[Route("/authentication/2fa/disable", name:"app_2fa_disable")]
    #[IsGranted("ROLE_USER")]
    public function disable2fa(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if(!$user)
        {
            return $this->redirectToRoute('app_logout');
        }

        $disable = ( 1== $request->get('disable'));
        if ($disable)
        {
            if ($user->isGoogleAuthenticatorEnabled()) 
            {
                $user->setTwoFactorEnabled(true);
                $user->setGoogleAuthenticatorSecret(null);
                $entityManager->flush();
            }
        }
        return $this->redirect('/admin?routeName=app_2fa_enable');
    }


    #[Route("/authentication/2fa/qr-code", name: "app_2fa_qr_code")]
    #[IsGranted("ROLE_USER")]
    public function displayGoogleAuthenticatorQrCodeSVG(GoogleAuthenticatorInterface $googleAutInterface)
    {
        $qrContent = $googleAutInterface->getQRContent($this->getUser());

        $result = Builder::create()
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->size(350)
            ->margin(5)
            ->writer(new SvgWriter())
            ->build();

        return new Response($result->getString(), 200, ['Content-Type' => 'image/svg+xml']);
    }
}
