<?php


namespace Server\Core;

use Server\Http\HttpRequest;
use Symfony\Component\Yaml\Yaml;

/**
 * ------------------------------------------------------
 * Class Router
 * ------------------------------------------------------
 *
 * Wires the router configuration file with proper
 * controllers and execute those with their respective
 * needs, either classes services, post arguments
 *
 * @author Benjamin Gil Flores
 * @version NaN
 * @package Server\Core
 */
class Router
{
    /**
     * @var HttpRequest Instance of HttpRequest Service
     */
    private HttpRequest $httpRequest;

    /**
     * Router constructor.
     *
     * Defines class properties
     *
     * @param HttpRequest $httpRequest
     */
    public function __construct(HttpRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;

        $this->executeActionForRoute();
    }

    /**
     * Finds and instantiate the controller wired with the
     * desired route with post parameters if needed
     *
     * @return void
     */
    private function executeActionForRoute () : void
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

    /**
     * Instantiate passed services and returns
     * them in an array
     *
     * @param array $services
     * @return array
     */
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