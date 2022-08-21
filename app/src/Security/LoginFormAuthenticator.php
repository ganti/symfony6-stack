<?php

namespace App\Security;

use App\Entity\User;
use App\Service\Log\LogUserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

#use App\Security\LoginFormAuthenticatorUserBadge;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager, LogUserService $log)
    {
        $this->entityManager = $entityManager;
        $this->log = $log;
    }

    public function authenticate(Request $request): Passport
    {
   
        $userIdentifier = $request->request->get('username', '');

        $request->getSession()->set(Security::LAST_USERNAME, $userIdentifier);

        return new Passport(
            new UserBadge($userIdentifier, function ($userIdentifier) {
                $user_username_exists = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $userIdentifier]);
                $user_email_exists = null;
                $activeParams = null;

                if (!$user_username_exists) {
                    $user_email_exists = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userIdentifier]);

                    if (!$user_email_exists) {
                        $this->log->login('User could not be found.', false);
                        throw new UserNotFoundException();
                    } else {
                        $user_username_exists = null;
                        $activeParams['email'] = $userIdentifier;
                    }
                } else {
                    $activeParams['username'] = $userIdentifier;
                }

                if ($activeParams != null) {
                    $activeParams['deletedAt'] = null;
                    $activeParams['isVerified'] = true;

                    $user = $this->entityManager->getRepository(User::class)->findOneBy($activeParams);

                    if (!$user) {
                        $this->log->login('Login attempt while eMail is not active', false);
                        throw new CustomUserMessageAuthenticationException('Login attempt while eMail is not active');
                    } else {
                        if ($user->isActive()) {
                            $this->log->login('', true);
                            return $user;
                        } else {
                            $this->log->login('User not active', false);
                            throw new UserNotFoundException();
                        }
                    }
                }
            }),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('admin'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
