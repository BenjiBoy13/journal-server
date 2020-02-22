<?php

namespace Server\Http;

use Symfony\Component\Yaml\Yaml;

class HttpRequest
{
    public function getRequest ()
    {
        $serverConfig = Yaml::parseFile('./config/server.yml');
        $requestedUri = $_SERVER['REQUEST_URI'];
        $requestedMethod = $_SERVER['REQUEST_METHOD'];

        if (isset($serverConfig['web_root'])) {
            $webRoot = $serverConfig['web_root'];

            $requestedUri = str_replace($webRoot, "", $requestedUri);
        }

        return array(
            'uri' => $requestedUri,
            'method' => $requestedMethod
        );
    }

    public function getUrlValues ()
    {
        return $_GET;
    }

    public function jsonResponse (int $code, string $msg, array $content = [])
    {
        header('Content-Type: application/json');
        http_response_code($code);

        $response = array(
            'code' => $code,
            'msg' => $msg,
            'content' => $content
        );

        echo json_encode($response);
    }

    /**
     * @return object|null
     */
    public function authenticated () : ?object
    {
        $jwt = new JWT();

        if (isset(apache_request_headers()['Authorization'])) {
            $token = apache_request_headers()['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $token, $matches)) {
                try {
                    return $jwt->verifyToken($matches[1]);
                } catch (\Exception $e) {
                    return null;
                }
            }

            return null;
        }
    }

    public function getJsonBodyFromRequest () : ?object
    {
        $data = file_get_contents("php://input");
        $data = json_decode($data);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }

        return null;
    }
}