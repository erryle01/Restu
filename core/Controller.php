<?php

class Controller
{
    protected function apiSuccess(
        mixed $data = null,
        int $status = 200
    ): never {
        ApiResponse::success($data, $status);
    }

    protected function apiError(
        int $status,
        string $code,
        string $message,
        array $details = []
    ): never {
        ApiResponse::error($status, $code, $message, $details);
    }

    protected function view(
        string $view,
        array $data = [],
        bool $layout = true
    ): void {

        if (ApiResponse::isRequest()) {
            ApiResponse::success($data);
        }

        extract($data);

        $viewFile = BASE_PATH . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: " . $viewFile);
        }

        if ($layout) {
            $contentView = $viewFile;

            require BASE_PATH . '/views/layouts/main.php';
        } else {
            require $viewFile;
        }
    }

    protected function redirect(string $url): never
    {
        if (ApiResponse::isRequest()) {
            ApiResponse::success([
                'redirect' => BASE_URL . $url
            ]);
        }

        header('Location: ' . BASE_URL . $url);
        exit;
    }
}