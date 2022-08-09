<?php

namespace App\Service\Log;

use App\Entity\User;
use App\Entity\Email as EntityEmail;
use App\Service\Log\LogService;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class LogMailerService extends LogService
{
    
    private ?bool $detailedMailLogActive = true;


    public function setDetailedMailLogActive(bool $detailedMailLogActive=true): self
    {
        $this->detailedMailLogActive = $detailedMailLogActive;
        return $this;
    }


    public function sendMail(Email $mail, ?String $message=null, ?bool $success=False, ?User $user = null) : self
    {
        $message = $message ? trim($message . PHP_EOL . $this->getMessagePropertyString($mail)) : $this->getMessagePropertyString($mail);

        if($user)
        {
            $this->log->setUser($user);
        }
        
        if($success)
        {
            $subContext = 'sent';
            $this->info('mailer', $subContext, $message, $success);
        }else{
            $subContext = 'not sent';
            $this->error('mailer', $subContext, $message, $success);
        }
        
        $this->setSentMailDetailedLog($mail, $success, $user);
        return $this;
    }


    private function getMessageProperties($message): ?Array
    {
        $collection = [];
        
        $collection['from'] = join(', ',
                                array_map(function($entry) {
                                    return $entry->getAddress();
                                }, $message->getFrom())
                        );
        $collection['to'] = join(', ',
                        array_map(function($entry) {
                            return $entry->getAddress();
                        }, $message->getTo())
                );

        $collection['cc'] = join(', ',
                            array_map(function($entry) {
                                return $entry->getAddress();
                            }, $message->getCc())
                        );

        $collection['bcc'] = join(', ',
                            array_map(function($entry) {
                                return $entry->getAddress();
                            }, $message->getBcc())
                        );
        $collection['subject'] = $message->getSubject();
        #$collection['bodyHTML'] = $message->__serialize(); 
        #$collection['bodyText'] = $message->getTextBody(); 

        return array_filter($collection);
    }

    private function getMessagePropertyString($message) : ?String
    {
        $return = "";
        foreach($this->getMessageProperties($message) as $key => $value)
        {
            if($value)
            {
                $return .= strtoupper($key) . ': ' . $value . PHP_EOL;
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
        
        if($this->detailedMailLogActive)
        {
            #$maillog->setHtml($properties['bodyHTML']);
        }
        if(!$success)
        {
            $maillog->setFailed(new \DateTime());
        }

        $this->manager->persist($maillog);
        $this->manager->flush();

    }
}