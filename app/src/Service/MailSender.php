<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Service\Log\LogMailerService;
use Symfony\Component\Mime\RawMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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
       $this->log = $log;
       $this->log->setDetailedMailLogActive($params->get('app')['mailer']['detailed_mail_log']);

       $this->fromMail = $params->get('app')['mailer']['from_email'];
       $this->fromName = $params->get('app')['mailer']['from_name'];

    }

    public function sendMail(Email $message, ?User $user = null) : void
    {
        $message->from( $this->fromName == null ? new Address($this->fromMail) : new Address($this->fromMail, $this->fromName));
        try
        {
            $this->mailer->send($message);
            $this->log->sendMail($message, null, True, $user);
        } catch (TransportExceptionInterface $e) {
            $this->log->sendMail($message, $e->getMessage(), False, $user);
        } 
    }

}