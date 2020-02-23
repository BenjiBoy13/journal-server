<?php

namespace Server\Http;

use Exception;
use Server\Core\YamlParser;

class HttpRequest
{
    public function getRequest ()
    {
        $ymlParser = new YamlParser();
        $serverConfig = $ymlParser->parseIt('./config/server.yml');
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

    public function sanitizeData (array $data) : array
    {
        $allowedHtmlTags = "<p><h1><h2><h3><h4><h5><h6><b><strong><blockquote><a><hr><br>";
        foreach ($data as $key => $value) {
            $sanitizedString = strip_tags($value, $allowedHtmlTags);
            $sanitizedString = htmlentities($sanitizedString, ENT_QUOTES, 'UTF-8');
            $data[$key] = $sanitizedString;
        }

        return $data;
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
                } catch (Exception $e) {
                    return null;
                }
            }

            return null;
        }

        return null;
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