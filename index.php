<?php

require_once './vendor/autoload.php';

use Server\Core\YamlParser;
use Server\Kernel;

error_reporting(E_ALL);
ini_set('display_errors', '1');

$dotEnv = new Symfony\Component\Dotenv\Dotenv();
$dotEnv->load(__DIR__ . "/.env");

function diary_exception_handler (Throwable $exception)
{
    $yamlParser = new YamlParser();
    $serverConfig = $yamlParser->parseIt('./config/server.yml');
    header('Content-Type: application/json');
    http_response_code(500);

    if ($serverConfig['debug']) {
        $response = array(
            'code' => 500,
            'exception' => array(
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            )
        );
    } else {
        $response = array(
            'code' => 500,
            'msg' => 'An unexpected error occur',
            'exception' => 'In order to see what went wrong, enable debug mode on server'
        );
    }

    echo json_encode($response);
}

set_exception_handler('diary_exception_handler');

new Kernel();
