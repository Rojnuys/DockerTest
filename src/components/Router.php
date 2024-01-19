<?php

class Router
{
    private $routes;

    public function __construct()
    {
        $this->routes = include ROOT . '/config/routes.php';
    }

    public function run()
    {
        $uri = null;

        if (empty($_SERVER['REQUEST_URI'])) {
            $uri = '/';
        } else {
            $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
        }

        foreach ($this->routes as $uriPattern => $path) {
            if (preg_match("~$uriPattern~", $uri)) {
                $path = preg_replace("~$uriPattern~", $path, $uri);
                $params = explode('/', $path);

                $controllerName = ucfirst(array_shift($params)) . 'Controller';
                $controllerPath = ROOT . '/controllers/' . $controllerName . '.php';

                if (!file_exists($controllerPath)) {
                    // error 500 incorrect route path
                }

                include_once $controllerPath;
                $controller = new $controllerName();
                $actionName = array_shift($params) . 'Action';

                if (!method_exists($controller, $actionName)) {
                    // error 500 incorrect route path
                }

                $controller->$actionName(...$params);
                break;
            }
        }
    }
}