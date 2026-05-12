<?php

namespace App\Exceptions;

use Exception;

class UnsupportedWebserverException extends Exception
{
    public function __construct($webserverName)
    {
        parent::__construct("Webserver '{$webserverName}' não suportado.");
    }
}
