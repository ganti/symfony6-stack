<?php

declare(strict_types=1);

namespace App\Service\Log;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class LogService extends AbstractController
{
    protected Log $log;
    private Security $security;
    protected RequestStack $requestStack; 
    protected EntityManagerInterface $manager;

    /**
     * LogService constructor.
     * @param EntityManagerInterface $manager
     * @param Security $security
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $manager, Security $security, RequestStack $requestStack)
    {
        $this->manager = $manager;
        $this->security = $security;
        $this->requestStack = $requestStack;

        $this->log = new Log();
        $this->log->setUser( $this->security->getUser() );

        if (!empty($this->requestStack->getCurrentRequest())){
            $this->log->setClientIP( $this->requestStack->getCurrentRequest()->getClientIp() );
            $this->log->setClientLocale( $this->requestStack->getCurrentRequest()->getLocale() );
            $this->log->setRequestMethod( $this->requestStack->getCurrentRequest()->getMethod() );
            $this->log->setRequestPath( $this->requestStack->getCurrentRequest()->getPathInfo() );
        }
        
    }

    private function logEvent(?String $level, ?String $context, ?String $subcontext = null, ?String $message, ?bool $isSuccess = null): self
    {
        $this->log->setLevel($level);
        $this->log->setContext($context);
        $this->log->setSubcontext($subcontext);
        $this->log->setMessage($message);
        $this->log->setSuccess($isSuccess);

        $this->manager->persist($this->log);
        $this->manager->flush();
        return $this;
    }

    public function info(?String $context, ?String $subcontext, ?String $message, ?bool $isSuccess = null): self
    {
        $this->logEvent('INFO', $context, $subcontext, $message, $isSuccess);
        return $this;
    }

    public function debug(?String $context, ?String $subcontext, ?String $message, ?bool $isSuccess = null): self
    {
        $this->logEvent('DEBUG', $context, $subcontext, $message, $isSuccess);
        return $this;
    }

    public function warning(?String $context, ?String $subcontext, ?String $message, ?bool $isSuccess = False): self
    {
        $this->logEvent('WARNING', $context, $subcontext, $message, $isSuccess);
        return $this;
    }

    public function error(?String $context, ?String $subcontext, ?String $message, ?bool $isSuccess = False): self
    {
        $this->logEvent('ERROR', $context, $subcontext, $message, $isSuccess);
        return $this;
    }


}