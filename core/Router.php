<?php

class Router
{
    private array $routes = [];

    /**
     * Register GET route
     */
    public function get(
        string $path,
        string $handler
    ): void {
        $this->add('GET', $path, $handler);
    }

    /**
     * Register POST route
     */
    public function post(
        string $path,
        string $handler
    ): void {
        $this->add('POST', $path, $handler);
    }

    /**
     * Add route
     */
    private function add(
        string $method,
        string $path,
        string $handler
    ): void {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => trim($path, '/'),
            'handler' => $handler
        ];
    }

    /**
     * Dispatch request
     */
    public function dispatch(
        string $requestUri,
        string $requestMethod
    ): void {

        try {
            $this->dispatchRequest($requestUri, $requestMethod);
        } catch (Throwable $exception) {
            if (ApiResponse::isRequest()) {
                ApiResponse::error(
                    500,
                    'INTERNAL_SERVER_ERROR',
                    'An unexpected server error occurred.'
                );
            }

            throw $exception;
        }
    }

    private function dispatchRequest(
        string $requestUri,
        string $requestMethod
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Get URL path
        |--------------------------------------------------------------------------
        */

        $path = parse_url(
            $requestUri,
            PHP_URL_PATH
        );

        $path = trim($path, '/');

        /*
        |--------------------------------------------------------------------------
        | Remove BASE_URL
        |--------------------------------------------------------------------------
        |
        | /Restu/users/edit/1
        |
        | becomes:
        |
        | users/edit/1
        |
        */

        $baseUrl = trim(
            BASE_URL,
            '/'
        );

        if (
            $baseUrl !== '' &&
            (
                $path === $baseUrl ||
                str_starts_with(
                    $path,
                    $baseUrl . '/'
                )
            )
        ) {

            $path = trim(
                substr(
                    $path,
                    strlen($baseUrl)
                ),
                '/'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Find matching route
        |--------------------------------------------------------------------------
        */

        foreach ($this->routes as $route) {

            /*
            |--------------------------------------------------------------------------
            | Check HTTP method
            |--------------------------------------------------------------------------
            */

            if (
                strtoupper($route['method']) !==
                strtoupper($requestMethod)
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Convert route parameters
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | /users/edit/{id}
            |
            | becomes:
            |
            | users/edit/([^/]+)
            |
            */

            $routePath = trim(
                $route['path'],
                '/'
            );

            $pattern = preg_replace(
                '/\{([a-zA-Z0-9_]+)\}/',
                '([^/]+)',
                $routePath
            );

            $pattern = '#^' . $pattern . '$#';

            /*
            |--------------------------------------------------------------------------
            | Match URL
            |--------------------------------------------------------------------------
            */

            if (
                preg_match(
                    $pattern,
                    $path,
                    $matches
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | Remove full match
                |--------------------------------------------------------------------------
                */

                array_shift($matches);

                /*
                |--------------------------------------------------------------------------
                | Call controller and pass parameters
                |--------------------------------------------------------------------------
                */

                $this->callHandler(
                    $route['handler'],
                    $matches
                );

                return;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 404
        |--------------------------------------------------------------------------
        */

        if (ApiResponse::isRequest()) {
            ApiResponse::error(
                404,
                'ROUTE_NOT_FOUND',
                'The requested endpoint was not found.'
            );
        }

        http_response_code(404);

        echo '<h1>404 - Page Not Found</h1>';
        echo '<hr>';

        echo '<strong>Requested URL:</strong> ';
        echo htmlspecialchars($requestUri);

        echo '<br>';

        echo '<strong>Parsed Path:</strong> ';
        echo htmlspecialchars($path);

        echo '<br>';

        echo '<strong>Method:</strong> ';
        echo htmlspecialchars($requestMethod);
    }

    /**
     * Call controller action
     */
    private function callHandler(
        string $handler,
        array $parameters = []
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Separate controller and action
        |--------------------------------------------------------------------------
        |
        | UsersController@edit
        |
        */

        [
            $controller,
            $action
        ] = explode(
            '@',
            $handler,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Search module controllers
        |--------------------------------------------------------------------------
        */

        $modulesPath =
            BASE_PATH . '/modules';

        $controllerFile = null;

        $iterator =
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $modulesPath,
                    FilesystemIterator::SKIP_DOTS
                )
            );

        foreach ($iterator as $file) {

            if (
                $file->isFile() &&
                $file->getFilename() ===
                $controller . '.php'
            ) {

                $controllerFile =
                    $file->getPathname();

                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Controller not found
        |--------------------------------------------------------------------------
        */

        if (!$controllerFile) {

            if (ApiResponse::isRequest()) {
                ApiResponse::error(
                    500,
                    'CONTROLLER_NOT_FOUND',
                    'The requested controller is not available.'
                );
            }

            http_response_code(500);

            die(
                'Controller not found: ' .
                htmlspecialchars($controller)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Load controller
        |--------------------------------------------------------------------------
        */

        require_once $controllerFile;

        /*
        |--------------------------------------------------------------------------
        | Check controller class
        |--------------------------------------------------------------------------
        */

        if (!class_exists($controller)) {

            if (ApiResponse::isRequest()) {
                ApiResponse::error(
                    500,
                    'CONTROLLER_CLASS_NOT_FOUND',
                    'The requested controller is not available.'
                );
            }

            http_response_code(500);

            die(
                'Controller class not found: ' .
                htmlspecialchars($controller)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create controller object
        |--------------------------------------------------------------------------
        */

        $controllerObject =
            new $controller();

        /*
        |--------------------------------------------------------------------------
        | Check action
        |--------------------------------------------------------------------------
        */

        if (
            !method_exists(
                $controllerObject,
                $action
            )
        ) {

            if (ApiResponse::isRequest()) {
                ApiResponse::error(
                    500,
                    'ACTION_NOT_FOUND',
                    'The requested action is not available.'
                );
            }

            http_response_code(500);

            die(
                'Action not found: ' .
                htmlspecialchars($controller) .
                '@' .
                htmlspecialchars($action)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Call action
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | URL:
        | /users/edit/1
        |
        | Parameters:
        | [1]
        |
        | Controller call:
        |
        | $controllerObject->edit(1);
        |
        */

        $controllerObject->$action(
            ...$parameters
        );
    }
}