<?php

namespace App\Service;

use App\Service\LogService;
use App\Entity\User;

class LogUserService extends LogService
{
    public function login($message='', $success=False) : self
    {   
        $user = null;
        $requestUsername = $this->requestStack->getCurrentRequest()->get('username');
        $userByUsername =  $this->manager->getRepository(User::class)->findOneBy(['username' => $requestUsername]);
        
        if ($userByUsername != null)
        {
            $user = $userByUsername;
        }else{
            $userByEmail =  $this->manager->getRepository(User::class)->findOneBy(['email' => $requestUsername]);
            $user = $userByEmail;
        }
        if ($user != null) {
            $this->log->setUser($user);
        }

        if ($success) {
            $this->debug('user', 'login', $requestUsername.' login successful '.$message, True);
        }else{
            $this->info('user', 'login', $requestUsername.' failed logging in '.$message, False);
        }
        return $this;
    }

    public function logout($user, $success=False) : self
    {   
        $this->log->setUser($user);
        $this->debug('user', 'logout', $user.' logged out ', True);
        return $this;
    }

    public function passwordResetMail($success=False) : self
    {   
        $this->debug('user', 'sent password reset mail', $this->log->getUser.' logged out ', True);
        return $this;
    }




}