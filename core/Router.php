<?php

namespace App\Core;

class Router
{
    protected array $routes = [];

    public function addRoute(string $method, string $uri, array $controllerAction): void
    {
        $this->routes[$method][$uri] = $controllerAction;
    }

    public function dispatch(string $uri, string $method): void
    {
        if (isset($this->routes[$method][$uri])) {
            [$controllerClass, $action] = $this->routes[$method][$uri];

            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                if (method_exists($controllerInstance, $action)) {
                    $controllerInstance->$action();
                } else {
                    $this->notFound("Action {$action} not found in controller {$controllerClass}");
                }
            } else {
                $this->notFound("Controller class {$controllerClass} not found");
            }
        } else {
            $this->notFound("No route found for URI: {$uri} with method: {$method}");
        }
    }

    protected function notFound(string $message = "404 Not Found")
    {
        http_response_code(404);
        // Basit bir 404 sayfası veya mesajı gösterilebilir
        echo $message;
        exit;
    }

    // Rota tanımlamayı kolaylaştıran yardımcı metodlar
    public function get(string $uri, array $controllerAction): void
    {
        $this->addRoute('GET', $uri, $controllerAction);
    }

    public function post(string $uri, array $controllerAction): void
    {
        $this->addRoute('POST', $uri, $controllerAction);
    }

} 