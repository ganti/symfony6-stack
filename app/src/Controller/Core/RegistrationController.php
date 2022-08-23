<?php

namespace App\Controller\Core;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use App\Service\Log\LogUserService;
use Symfony\Component\Mime\Address;
use App\Form\Core\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\Core\ResetPasswordRequestFormType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier, LogUserService $log, TranslatorInterface $translator)
    {
        $this->emailVerifier = $emailVerifier;
        $this->log = $log;
        $this->translator = $translator;
    }

    private function t($message, $params=[])
    {
        return $this->translator->trans($message, $params, 'core');
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $this->isActive = isset($this->getParameter('app')['core']['registration_active']) ? $this->getParameter('app')['core']['registration_active'] : false;
        if (!$this->isActive) {
            return $this->redirectToRoute('app_login');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (empty($user->getUsername())) {
                $user->setUsername($user->getEmail());
            }
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setActive(true);
            $user->setTimezone($this->getParameter('app')['timezone']); //Load global timezone
            $user->setDateFormat($this->getParameter('app')['date_format']); //Load global date format
            $user->setTimeFormat($this->getParameter('app')['time_format']); //Load global time format
            $user->setLocale($this->getParameter('app')['default_locale']); //Load global locale


            try {
                $entityManager->persist($user);
                $entityManager->flush();
            } finally {
                $this->log->user_created($user);
            }

            $this->sendVerificationMail($user);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('view/core/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    private function sendVerificationMail(?User $user)
    {
        $sendTo = empty($user->getFullName()) ? new Address($user->getEmail()) : new Address($user->getEmail(), $user->getFullName());
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->to($sendTo)
                ->subject($this->t('service.registration.email.subject'))
                ->htmlTemplate('email/core/registration_confirmation_email.html.twig')
        );
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $this->isActive = isset($this->getParameter('app')['core']['registration_active']) ? $this->getParameter('app')['core']['registration_active'] : false;
        if (!$this->isActive) 
        {
            return $this->redirectToRoute('app_login');
        }

        $pid = $request->get('id');

        if (null === $pid) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->findOneBy(['pid' => $pid]);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_login');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }


    #[Route('/request-verify-email', name: 'app_request_verify_email')]
    public function requestVerifyUserEmail(Request $request, UserRepository $userRepository): Response {

        if ($this->getUser())
        {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $user =  $userRepository->findOneByEmail($form->get('email')->getData());
            if ($user)
            {
                $this->sendVerificationMail($user);
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('view/core/resend_verifcation/resend_verifcation.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }
}
