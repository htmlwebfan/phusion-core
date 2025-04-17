<?php

class Router {
    private $controller;
    private $action;
    private $params = [];

    public function dispatch() {
        $uri = trim($_SERVER['REQUEST_URI'], '/');
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $base = dirname($scriptName);
        if ($base !== '/' && !empty($base)) {
            $uri = preg_replace("#^" . preg_quote($base, '#') . "/?#", '', $uri);
        }
        $uri = rtrim($uri, '/');
        $method = $_SERVER['REQUEST_METHOD'];
        $config = include __DIR__ . '/../config.php';
        $routes = $config['routes'] ?? [];

        error_log("URI: $uri, Method: $method");
        if (empty($routes)) {
            // error_log("Error: No routes defined in config.php");
            $this->routeToError(500, "No routes defined");
            return;
        }

        $matched = false;
        foreach ($routes as $pattern => $methods) {
            $regex = $pattern;
            $regex = preg_replace('#\(:any\)#', '([^/]+)', $regex);
            $regex = preg_replace('#\(:num\)#', '(\d+)', $regex);
            $regex = "#^$regex$#"; // Anchor the pattern
            // error_log("Pattern: $pattern, Regex: $regex");
            if (preg_match($regex, $uri, $matches)) {
                if (isset($methods[$method])) {
                    list($controllerName, $action, $middleware) = $methods[$method];
                    $this->params = array_slice($matches, 1);
                    $matched = true;
                    error_log("Matched: $pattern -> $controllerName::$action");
                    break;
                }
            }
        }

        if (!$matched) {
            $this->routeToError(404, "Route '$uri' not found for method $method");
            return;
        }

        if (class_exists($controllerName)) {
            $this->controller = new $controllerName();
            if (method_exists($this->controller, $action)) {
                $this->action = $action;
            } else {
                $this->routeToError(404, "Action '$action' not found in $controllerName");
                return;
            }
        } else {
            $this->routeToError(404, "Controller '$controllerName' not found");
            return;
        }

        $middlewareList = $config['middleware'] ?? [];
        foreach ($middleware as $name) {
            if (isset($middlewareList[$name])) {
                $middlewareList[$name]($this->controller);
            }
        }

        call_user_func_array([$this->controller, $this->action], $this->params);
    }

    private function routeToError($code, $message) {
        $this->controller = new ErrorController();
        $this->controller->error($code, $message);
    }
}