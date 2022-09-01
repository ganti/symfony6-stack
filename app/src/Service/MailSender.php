<?php

namespace App\Service;

use App\Entity\User;
use Twig\Environment;
use Html2Text\Html2Text;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Service\Log\LogMailerService;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class MailSender
{
    public function __construct(
        private TransportInterface $mailer,
        private Environment $twig,
        private ContainerBagInterface $params,
        private LogMailerService $log
    ) {
        $this->log = $log;
        $params = $params->get('app')['mailer'];
        $this->log->setMailLogging($params['logging_general'], $params['logging_full']);

        $this->fromMail = $params['from_email'];
        $this->fromName = $params['from_name'];
        $this->textMailLinkFomat  = $params['text_body']['link_format'] ?? 'table';
        $this->textMailWidth  = $params['text_body']['width'] ?? '70';
    }

    public function sendMail(Email $message, ?User $user = null): void
    {
        //If template is used, render it
        if (!empty($message->getHtmlTemplate())){
            $renderedHtmlBody = $this->twig->render($message->getHtmlTemplate(), $message->getContext());
            $message->html($renderedHtmlBody);
            $textContent = new Html2Text($renderedHtmlBody, ['do_links' => $this->textMailLinkFomat, 'width' => $this->textMailWidth]);
            $message->text(trim($textContent->getText()));
        }

        $message->from($this->fromName == null ? new Address($this->fromMail) : new Address($this->fromMail, $this->fromName));
        try {
            $message = $this->mailer->send($message);
            $this->log->sendMail($message, null, true, $user);
        } catch (TransportExceptionInterface $e) {
            $this->log->sendMail($message, $e->getMessage(), false, $user);
        }
    }
}
