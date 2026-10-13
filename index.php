<?php

require_once __DIR__ . '/config/config.php';

require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/ApiResponse.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/View.php';
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Router.php';

Session::start();

if (
    ApiResponse::isRequest() &&
    in_array(
        strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
        ['POST', 'PUT', 'PATCH'],
        true
    ) &&
    str_contains(
        $_SERVER['CONTENT_TYPE'] ?? '',
        'application/json'
    )
) {
    $body = file_get_contents('php://input');
    $payload = json_decode($body ?: '', true);

    if (!is_array($payload)) {
        ApiResponse::error(
            400,
            'INVALID_JSON',
            'The request body must contain valid JSON.'
        );
    }

    $_POST = $payload;
}

$router = new Router();

require BASE_PATH . '/config/routes.php';

$router->dispatch(
    $_SERVER['REQUEST_URI'],
    $_SERVER['REQUEST_METHOD']
);