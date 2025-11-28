<?php
namespace Core;

class Router {
    
    protected $routes = [];
    
    public function add($method, $route, $action) {
       // echo "test";
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => trim($route, '/'),
            'action' => $action
        ];
        
    }

    public function dispatch($url, $method) {
        //echo "URL: " . $url . " | Method: " . $method . "<br>"; // Debug output
    
        foreach ($this->routes as $route) {
            if ($route['route'] == trim($url, '/') && $route['method'] == strtoupper($method)) {
                if (is_callable($route['action'])) {
                    return call_user_func($route['action']);
                } elseif (is_string($route['action'])) {
                    return $this->executeController($route['action']);
                }
            }
        }
        echo "404 Not Found";
    }
    

    private function executeController($action) {
        list($controller, $method) = explode('@', $action);
        $controller = "App\\Controllers\\$controller";
        if (class_exists($controller) && method_exists($controller, $method)) {
            return (new $controller)->$method();
        }
        echo "Controller or method not found.";
    }
    
}

