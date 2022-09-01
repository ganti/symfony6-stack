<?php

namespace App\Service\Log;

use App\Entity\User;
use App\Service\Log\LogService;
use Symfony\Component\Mime\Email;
use App\Entity\Email as EntityEmail;
use Symfony\Component\Mailer\SentMessage;

class LogMailerService extends LogService
{
    private bool $generalLogging = false;
    private bool $fullLogging = false;

    public function setMailLogging(bool $generalLogging, bool $fullLogging): self
    {
        $this->generalLogging = $generalLogging;
        $this->fullLogging = $fullLogging;
        return $this;
    }


    public function sendMail($mail, ?String $message=null, ?bool $success=false, ?User $user = null): self
    {
        $message = $this->getMessagePropertyString($mail);

        if ($user) {
            $this->log->setUser($user);
        }

        if ($success) {
            if($this->generalLogging){
                $subContext = 'sent';
                $this->info('mailer', $subContext, $message, $success);
            }
        } else {
            $subContext = 'not sent';
            $this->error('mailer', $subContext, $message, $success);
        }

        if($this->generalLogging){
            $this->setSentMailDetailedLog($mail, $success, $user);
        }
        return $this;
    }


    private function getMessageProperties($message): ?array
    {
        $collection = [];
        $collection['messageId'] = '';

        if ($message instanceof SentMessage)
        {   
            $collection['from'] = $message->getEnvelope()->getSender()->getAddress();

            $collection['to'] = join(
                ', ',
                array_map(function ($entry) {
                    return $entry->getAddress();
                }, $message->getEnvelope()->getRecipients())
            );

            $collection['messageId'] = $message->getMessageId();
            $collection['subject'] = $message->getOriginalMessage()->getHeaders()->get('Subject')->getValue();
            $collection['bodyHTML'] = $message->getOriginalMessage()->getHtmlBody();
            $collection['bodyText'] = $message->getOriginalMessage()->getTextBody();
        }else{
            $collection['from'] = join(
                ', ',
                array_map(function ($entry) {
                    return $entry->getAddress();
                }, $message->getFrom())
            );
            $collection['to'] = join(
                ', ',
                array_map(function ($entry) {
                    return $entry->getAddress();
                }, $message->getTo())
            );
            $collection['subject'] = $message->getSubject();
            $collection['bodyHTML'] = $message->getHtmlBody();
            $collection['bodyText'] = $message->getTextBody();
            
        }
        return array_filter($collection);
    }

    private function getMessagePropertyString($message, $withBody = false): ?String
    {
        $return = "";
        foreach ($this->getMessageProperties($message) as $key => $value) {
            if ($value) {
                if( ($withBody and str_contains($key, 'body')) or !str_contains($key, 'body') )
                {
                    $return .= strtoupper($key) . ': ' . $value . PHP_EOL;
                }
            }
        }
        return $return;
    }

    private function setSentMailDetailedLog($message, ?bool $success, ?User $user): void
    {
        $properties = $this->getMessageProperties($message);
        
        $maillog = new EntityEmail();
        $maillog->setSubject($properties['subject']);
        $maillog->setSenderEmail($properties['from']);
        $maillog->setRecieverEmail($properties['to']);
        
        if($this->fullLogging){
            $maillog->setHtml($properties['bodyHTML']);
            $maillog->setText($properties['bodyText']);
        }
        
        if(isset($properties['messageId'])){
            $maillog->setMessageId($properties['messageId']);
        }

        if ($user) {
            $maillog->setUser($user);
        }
        if (!$success) {
            $maillog->setFailed(new \DateTime());
        }

        $this->manager->persist($maillog);
        $this->manager->flush();
    }
}
