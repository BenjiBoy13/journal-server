<?php


namespace Server\Core;

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
        $notFound = true;
        $request = $this->httpRequest->getRequest();
        $applicationRoutes = Yaml::parseFile('./config/routes.yml');

        foreach ($applicationRoutes as $key => $routes) {
            $controller = new $routes['controller'];

            foreach ($routes['paths'] as $path) {
                if (strpos($request['uri'], "?")) {
                    $request['uri'] = explode('?', $request['uri'])[0];
                }

                if ($path['path'] == $request['uri'] && $path['method'] == $request['method']) {
                    $parameters = array();
                    $action = $path['action'];

                    if (isset($path['services'])) {
                        $parameters = $this->getServices($path['services']);
                    }

                    if (isset($path['parameters'])) {
                        $postArgs = array();

                        foreach ($path['parameters'] as $parameter) {
                            if (isset($_POST[$parameter])) {
                                $postArgs[$parameter] = $_POST[$parameter];
                            }
                        }

                        array_push($parameters, $postArgs);
                    }

                    $notFound = false;
                    \call_user_func_array(array($controller, $action), $parameters);
                    break 2;
                }
            }
        }

        if ($notFound) {
            $this->httpRequest->jsonResponse(404, "Page not found");
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