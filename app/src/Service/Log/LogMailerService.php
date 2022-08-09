<?php

namespace App\Service\Log;

use App\Entity\User;
use App\Service\Log\LogService;
use Symfony\Component\Mime\RawMessage;

class LogMailerService extends LogService
{
    
    private ?bool $detailedMailLogActive = true;


    public function setDetailedMailLogActive(bool $detailedMailLogActive=true): self
    {
        $this->detailedMailLogActive = $detailedMailLogActive;
        return $this;
    }


    public function sendMail(?RawMessage $mail, ?String $message=null, ?bool $success=False, ?User $user = null) : self
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
        return $this;
    }


    private function getMessageProperties($message): ?Array
    {
        $collection = [];
        $collection['from'] = $message->getFrom()[0]->getAddress();
        $collection['subject'] = $message->getSubject();
        
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
}