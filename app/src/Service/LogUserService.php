<?php

namespace App\Service;

use App\Service\LogService;
use App\Entity\User;

class LogUserService extends LogService
{
    public function login($message='', $success=False) : self
    {   
        $user = null;
        $userIdentifier = $this->requestStack->getCurrentRequest()->get('email');
        $userByEmail =  $this->manager->getRepository(User::class)->findOneBy(['email' => $userIdentifier]);
        
        if ($userByEmail == null)
        {
            $userByUsername =  $this->manager->getRepository(User::class)->findOneBy(['username' => $userIdentifier]);
            $user = $userByUsername;
        }else{
            $user = $userByEmail;
        }
        if ($user != null) {
            $this->log->setUser($user);
        }

        if ($success) {
            $this->debug('user', 'login', $userIdentifier.' login successful '.$message, True);
        }else{
            $this->info('user', 'login', $userIdentifier.' failed logging in '.$message, False);
        }
        return $this;
    }

    public function logout($user) : self
    {   
        $this->log->setUser($user);
        $this->debug('user', 'logout', $user.' logged out ', True);
        return $this;
    }

    public function user_created($user) : self
    {   
        $this->log->setUser($user);
        $this->debug('user', 'created', $user.' created', True);
        return $this;
    }

    public function user_emailverified($user) : self
    {   
        $this->log->setUser($user);
        $this->debug('user', 'email verified', $user->getEmail().' verifed', True);
        return $this;
    }

    
    public function passwordResetMail($success=False) : self
    {   
        $this->debug('user', 'sent password reset mail', $this->log->getUser.' logged out ', True);
        return $this;
    }


}