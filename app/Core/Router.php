<?php
namespace App\Core;

use App\Exceptions\AppException;

class Router
{
    private static array $routes = [];
    private static array $namedRoutes = [];
    private static string $groupPrefix = '';
    private static array $groupMiddlewares = [];

    public static function get(string $uri, string|array|callable $action, array $middlewares = []): void
    {
        self::addRoute('GET', $uri, $action, $middlewares);
    }

    public static function post(string $uri, string|array|callable $action, array $middlewares = []): void
    {
        self::addRoute('POST', $uri, $action, $middlewares);
    }

    public static function any(string $uri, string|array|callable $action, array $middlewares = []): void
    {
        self::addRoute('GET', $uri, $action, $middlewares);
        self::addRoute('POST', $uri, $action, $middlewares);
    }

    public static function group(array $attributes, callable $callback): void
    {
        $previousPrefix = self::$groupPrefix;
        $previousMiddlewares = self::$groupMiddlewares;

        if (isset($attributes['prefix'])) {
            self::$groupPrefix = $previousPrefix . '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $middlewares = (array)$attributes['middleware'];
            self::$groupMiddlewares = array_merge($previousMiddlewares, $middlewares);
        }

        $callback();

        self::$groupPrefix = $previousPrefix;
        self::$groupMiddlewares = $previousMiddlewares;
    }

    private static function addRoute(string $method, string $uri, string|array|callable $action, array $middlewares = []): void
    {
        $cleanUri = self::$groupPrefix . '/' . trim($uri, '/');
        $cleanUri = '/' . trim($cleanUri, '/');
        if ($cleanUri === '//') {
            $cleanUri = '/';
        }

        $mergedMiddlewares = array_merge(self::$groupMiddlewares, $middlewares);

        self::$routes[] = [
            'method' => strtoupper($method),
            'uri' => $cleanUri,
            'action' => $action,
            'middlewares' => $mergedMiddlewares
        ];
    }

    public static function dispatch(string $requestUri, string $requestMethod): void
    {
        $parsedUrl = parse_url($requestUri, PHP_URL_PATH);
        
        // Strip project subfolder path if running inside /pub-campusmate/public
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptName !== '/' && !empty($scriptName) && str_starts_with($parsedUrl, $scriptName)) {
            $parsedUrl = substr($parsedUrl, strlen($scriptName));
        }

        $cleanUri = '/' . trim($parsedUrl, '/');
        $method = strtoupper($requestMethod);

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue;
            }

            // Convert route pattern with {param} into regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['uri']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $cleanUri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run Middleware Pipeline
                self::runMiddlewares($route['middlewares']);

                // Execute Action
                self::executeAction($route['action'], $params);
                return;
            }
        }

        // Route Not Found - 404
        self::handleNotFound($cleanUri);
    }

    private static function runMiddlewares(array $middlewares): void
    {
        $middlewareMap = [
            'auth' => \App\Middleware\AuthMiddleware::class,
            'role' => \App\Middleware\RoleMiddleware::class,
            'csrf' => \App\Middleware\CsrfMiddleware::class,
        ];

        foreach ($middlewares as $mw) {
            $param = null;
            if (str_contains($mw, ':')) {
                [$mwName, $param] = explode(':', $mw, 2);
            } else {
                $mwName = $mw;
            }

            if (isset($middlewareMap[$mwName])) {
                $class = $middlewareMap[$mwName];
                $instance = new $class();
                $instance->handle($param);
            }
        }
    }

    private static function executeAction(string|array|callable $action, array $params = []): void
    {
        if (is_callable($action)) {
            call_user_func_array($action, $params);
            return;
        }

        if (is_array($action)) {
            [$controllerClass, $method] = $action;
            $controller = new $controllerClass();
            call_user_func_array([$controller, $method], $params);
            return;
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$controllerName, $method] = explode('@', $action, 2);
            $fullClass = str_contains($controllerName, '\\') ? $controllerName : "App\\Controllers\\{$controllerName}";
            if (!class_exists($fullClass)) {
                throw new AppException("Controller class [{$fullClass}] not found.");
            }
            $controller = new $fullClass();
            if (!method_exists($controller, $method)) {
                throw new AppException("Method [{$method}] not found in controller [{$fullClass}].");
            }
            call_user_func_array([$controller, $method], $params);
            return;
        }

        throw new AppException("Invalid route action specified.");
    }

    private static function handleNotFound(string $uri): void
    {
        http_response_code(404);
        try {
            View::render('errors/404', ['uri' => $uri, 'title' => 'Page Not Found - PUB CampusMate']);
        } catch (\Throwable $e) {
            echo "<!DOCTYPE html><html><head><title>404 Not Found</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'></head><body class='bg-light d-flex align-items-center' style='min-height:100vh;'><div class='container text-center'><h1 class='display-1 fw-bold text-primary'>404</h1><h3 class='fw-bold'>Page Not Found</h3><p class='text-muted'>The requested path <code>" . htmlspecialchars($uri) . "</code> was not found on this server.</p><a href='/' class='btn btn-primary mt-3'>Return to Home</a></div></body></html>";
        }
        exit;
    }

    public static function getRoutes(): array
    {
        return self::$routes;
    }
}
