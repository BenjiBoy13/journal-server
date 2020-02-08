<?php


namespace Server;

use Server\Core\Router;
use Server\Http\HttpRequest;

class Kernel
{
    public function __construct()
    {
        new Router(new HttpRequest());
    }
}

