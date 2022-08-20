<?php

namespace App\Controller\Admin;

use Symfony\Component\Translation\TranslatableMessage;

if (!\function_exists(t::class)) {
    
    function t(string $message, array $parameters = []): TranslatableMessage
    {
        return new TranslatableMessage($message, $parameters, 'admin');
    }
}
