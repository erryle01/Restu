<?php

class ApiResponse
{
    public static function isRequest(): bool
    {
        $path = (string) parse_url(
            $_SERVER['REQUEST_URI'] ?? '',
            PHP_URL_PATH
        );

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        $path = trim($path, '/');

        $baseUrl = trim(BASE_URL, '/');

        if (
            $baseUrl !== '' &&
            ($path === $baseUrl || str_starts_with($path, $baseUrl . '/'))
        ) {
            $path = trim(substr($path, strlen($baseUrl)), '/');
        }

        return $path === 'api' ||
            str_starts_with($path, 'api/') ||
            str_contains($accept, 'application/json');
    }

    public static function error(
        int $status,
        string $code,
        string $message,
        array $details = []
    ): never {
        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details
            ]
        ], JSON_UNESCAPED_SLASHES);

        exit;
    }

    public static function success(
        mixed $data = null,
        int $status = 200
    ): never {
        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => true,
            'data' => $data,
            'error' => null
        ], JSON_UNESCAPED_SLASHES);

        exit;
    }
}