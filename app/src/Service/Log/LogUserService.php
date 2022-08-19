<?php

namespace App\Service\Log;

use App\Entity\User;
use App\Service\Log\LogService;

class LogUserService extends LogService
{
    public function login($message='', $success=false): self
    {
        $user = null;
        $userIdentifier = $this->requestStack->getCurrentRequest()->get('email');
        $userByEmail = $this->manager->getRepository(User::class)->findOneBy(['email' => $userIdentifier]);

        if ($userByEmail == null) {
            $userByUsername = $this->manager->getRepository(User::class)->findOneBy(['username' => $userIdentifier]);
            $user = $userByUsername;
        } else {
            $user = $userByEmail;
        }
        if ($user) {
            $this->log->setUser($user);
        }

        if ($success) {
            $this->debug('user', 'login', $userIdentifier.' login successful '.$message, true);
        } else {
            $this->info('user', 'login', $userIdentifier.' failed logging in '.$message, false);
        }
        return $this;
    }

    public function logout($user): self
    {
        $this->log->setUser($user);
        $this->debug('user', 'logout', $user.' logged out ', true);
        return $this;
    }

    public function user_created($user): self
    {
        $this->log->setUser($user);
        $this->debug('user', 'created', $user.' created', true);
        return $this;
    }

    public function user_emailverified($user): self
    {
        $this->log->setUser($user);
        $this->debug('user', 'email verified', $user->getEmail().' verifed', true);
        return $this;
    }


    public function passwordResetMail($success=false): self
    {
        $this->debug('user', 'sent password reset mail', $this->log->getUser().' logged out ', true);
        return $this;
    }
}
