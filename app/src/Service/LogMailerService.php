<?php

namespace App\Service;

use App\Entity\User;
use App\Service\LogService;
use Symfony\Component\Mime\RawMessage;

class LogMailerService extends LogService
{

    public function __construct(?bool $detailedMailLogActive=true)
    {
        $this->detailedMailLogActive = $detailedMailLogActive;
    }
    
    public function sendMail(?RawMessage $mail, ?String $message='', ?bool $success=False, ?User $user = null) : self
    {
        if($message)
        {
            $message = trim($message . PHP_EOL . $this->getMessagePropertyString($mail));
        }

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