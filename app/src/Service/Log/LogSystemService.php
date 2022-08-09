<?php

namespace App\Service\Log;

use App\Service\Log\LogService;

class LogSystemService extends LogService
{

    public function fixtures($subContext='loaded' ,$message='', $success=False) : self
    {
        $this->debug('fixtures', $subContext, $message, $success);
        return $this;
    }

}