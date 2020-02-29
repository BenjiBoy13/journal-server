<?php

namespace Server\Api\Controllers;

use Server\Http\HttpRequest;

class RootController extends BaseController
{
    public function indexAction ()
    {
        $this->httpRequest->jsonResponse(200, "My Journal Official Application Programming Interface (API)");
    }
}
