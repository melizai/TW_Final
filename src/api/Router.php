<?php

class Router
{
    private $routes = [
        'GET' => [
            '/api/shows/{id}' => ['controller' => 'ShowsController', 'method' => 'getById'],
            '/api/search/shows' => ['controller' => 'ShowsController', 'method' => 'search'],
            '/api/shows' => ['controller' => 'ShowsController', 'method' => 'getAll'],
            '/api/filters/shows' => ['controller' => 'ShowsController', 'method' => 'getFilters'],
            '/api/statistics/top-rated' => ['controller' => 'StatisticsController', 'method' => 'getTopRated'],
            '/api/statistics/popular' => ['controller' => 'StatisticsController', 'method' => 'getPopular'],
            '/api/reviews/{id}' => ['controller' => 'ReviewsController', 'method' => 'getByShowId'],
            '/api/reviews' => ['controller' => 'ReviewsController', 'method' => 'getAll'],
            '/api/news' => ['controller' => 'NewsController', 'method' => 'getAll'],
        ],
        'POST' => [
            '/api/reviews' => ['controller' => 'ReviewsController', 'method' => 'add'],
            '/api/users/login' => ['controller' => 'UsersController', 'method' => 'login'],
            '/api/users/logout' => ['controller' => 'UsersController', 'method' => 'logout'],
            '/api/users/register' => ['controller' => 'UsersController', 'method' => 'register'],
            '/api/shows' => ['controller' => 'ShowsController', 'method' => 'add'],
            '/api/shows/import' => ['controller' => 'ShowsController', 'method' => 'import'],
        ],
        'PUT' => [
            '/api/shows/{id}' => ['controller' => 'ShowsController', 'method' => 'update']
        ],
        'DELETE' => [
            '/api/reviews/{id}' => ['controller' => 'ReviewsController', 'method' => 'delete']
        ]
    ];

    public function route($method, $url) {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? '';

        foreach ($this->routes[$method] as $routePath => $routeInfo) {
            $pattern = $this->convertToRegex($routePath);

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove the full match

                parse_str($query, $queryParams);

                $controller = $routeInfo['controller'];
                $methodToCall = $routeInfo['method'];

                $this->callController($controller, $methodToCall, $matches, $queryParams);
                return;
            }
        }

        // Handle 404 - No matching route found
        $this->callController('ErrorController', 'notFound');
    }

    protected function convertToRegex($routePath) {
        $pattern = preg_replace('/\//', '\\/', $routePath);
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_]+)', $pattern);
        $pattern = '/^' . $pattern . '$/';

        return $pattern;
    }

    protected function getRequestHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $headerName = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    protected function callController($controller, $methodToCall, $params = [], $queryParams = []) {
        // Assuming controllers are classes with a specified method to call
        require_once('Controllers/' . $controller . '.php');
        $controllerInstance = new $controller();
        if (method_exists($controllerInstance, $methodToCall)) {
            $controllerInstance->$methodToCall($params, $queryParams);
        } else {
            // Handle method not found in controller
            $this->callController('ErrorController', 'notFound');
        }
    }
}