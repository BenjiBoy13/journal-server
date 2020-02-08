<?php

namespace Server\Api\Controllers;

use Server\Http\HttpRequest;

class RootController
{
    public function indexAction (HttpRequest $httpRequest)
    {
        $httpRequest->jsonResponse(200, "My Journal Official Application Programming Interface (API)");
    }
}
