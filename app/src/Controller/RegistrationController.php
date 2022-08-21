<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\Log\LogUserService;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translator\TranslationInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier, LogUserService $log)
    {
        $this->emailVerifier = $emailVerifier;
        $this->log = $log;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
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


            // generate a signed url and email it to the user
            $sendTo = empty($user->getFullName()) ? new Address($user->getEmail()) : new Address($user->getEmail(), $user->getFullName());
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->to($sendTo)
                    ->subject($translator->trans('base.registration.email.subject'))
                    ->htmlTemplate('email/auth/registration_confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('views/auth/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
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

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
