<?php

namespace App\Core\Router;

class Router
{
    /**
     * @var array
     */
    private array $routes = [];

    /**
     * @var string
     */
    private string $controllerAction = 'execute';

    /**
     * @param string $method
     * @param string $path
     * @param callable|string $handler
     * @return void
     */
    public function addRoute(string $method, string $path, callable|string $handler): void
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    /**
     * @return void
     */
    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (is_callable($route['handler'])) {
                    call_user_func_array($route['handler'], $params);
                    return;
                }

                if (is_string($route['handler'])) {
                    $controllerClass = $route['handler'];
                    if (class_exists($controllerClass) && method_exists($controllerClass, $this->controllerAction)) {
                        $controller = new $controllerClass();
                        call_user_func_array([$controller, $this->controllerAction], $params);
                        return;
                    }
                }
            }
        }

        // Если ничего не найдено
        http_response_code(404);
        echo "404 Page Not Found";
    }

    /**
     * @param string $configFile
     * @return $this
     * @throws \Exception
     */
    public function loadRoutesFromXml(string $configFile): self
    {
        if (!file_exists($configFile)) {
            throw new \Exception("Routes config file not found: {$configFile}");
        }

        $xml = simplexml_load_file($configFile);

        foreach ($xml->router as $router) {
            foreach ($router->route as $route) {
               $this->addRoute(
                   (string)$route->method['value'],
                   (string)$route->alias['value'],
                   (string)$route->controller['value']);
            }
        }

        return $this;
    }


}