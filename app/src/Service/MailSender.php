<?php

namespace App\Service;

use App\Entity\User;
use App\Service\LogMailerService;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class MailSender
{

    public function __construct(
        private MailerInterface $mailer,
        private ContainerBagInterface $params,
        private LogMailerService $log
    ) {
       $this->log = new LogMailerService($params->get('app')['mailer']['detailed_mail_log']);
       $this->fromMail = $params->get('app')['mailer']['from_email'];
       $this->fromName = $params->get('app')['mailer']['from_name'];
    }

    public function sendMail(RawMessage $message, ?User $user = null) : void
    {
        $message->from( $this->fromName == null ? new Address($this->fromMail) : new Address($this->fromMail, $this->fromName));
        try
        {
            $this->mailer->send($message);
            $this->log->sendMail($message, '', True, $user);
        } catch (TransportExceptionInterface $e) {
            $this->log->sendMail($message, $e->getMessage(), False, $user);
        } 
    }


}