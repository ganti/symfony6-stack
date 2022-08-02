<?php

namespace App\Service;

use App\Service\LogService;
use App\Entity\User;

class LogSystemService extends LogService
{

    public function fixtures($subContext='loaded' ,$message='', $success=False) : self
    {
        $this->debug('fixtures', $subContext, $message, $success);
        return $this;
    }

}