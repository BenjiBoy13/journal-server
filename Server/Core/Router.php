<?php


namespace Server\Core;

use Grpc\Server;
use Server\Api\Controllers\RootController;
use Server\Http\HttpRequest;
use Symfony\Component\Yaml\Yaml;

class Router
{
    private HttpRequest $httpRequest;

    public function __construct(HttpRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;

        $this->executeActionForRoute();
    }

    private function executeActionForRoute ()
    {
        $request = $this->httpRequest->getRequest();
        $applicationRoutes = Yaml::parseFile('./config/routes.yml');

        foreach ($applicationRoutes as $key => $routes) {
            if ($request['method'] == $routes['method']) {
                $controller = new $routes['controller'];

                foreach ($routes['paths'] as $path) {
                    if ($path['path'] == $request['uri']) {
                        $services = array();
                        $action = $path['action'];

                        if (isset($path['services'])) {
                            $services = $this->getServices($path['services']);
                        }

                        \call_user_func_array(array($controller, $action), $services);
                        break 2;
                    }
                }
            }
        }
    }

    private function getServices (array $services) : array
    {
        $instancedServices = array();

        foreach ($services as $service) {
            if (class_exists($service)) {
                array_push($instancedServices, new $service);
            }
        }

        return $instancedServices;
    }
}