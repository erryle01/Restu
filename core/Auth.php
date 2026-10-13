<?php

class Auth
{
    public static function check(): bool
    {
        return Session::get('logged_in', false) === true;
    }

    public static function user(): ?array
    {
        return Session::get('auth_user');
    }

    public static function id(): ?int
    {
        return Session::get('auth_user_id');
    }

    public static function roles(): array
    {
        return Session::get('auth_roles', []);
    }

    public static function permissions(): array
    {
        return Session::get('auth_permissions', []);
    }

    public static function hasRole(string $role): bool
    {
        return in_array($role, self::roles(), true);
    }

    public static function can(string $permission): bool
    {
        return in_array(
            $permission,
            self::permissions(),
            true
        );
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {

            if (ApiResponse::isRequest()) {
                ApiResponse::error(
                    401,
                    'UNAUTHENTICATED',
                    'Authentication is required.'
                );
            }

            header(
                'Location: ' .
                BASE_URL .
                '/login'
            );

            exit;
        }
    }

    public static function requirePermission(
        string $permission
    ): void {

        self::requireLogin();

        if (!self::can($permission)) {

            if (ApiResponse::isRequest()) {
                ApiResponse::error(
                    403,
                    'FORBIDDEN',
                    'You do not have permission to perform this action.',
                    ['permission' => $permission]
                );
            }

            http_response_code(403);

            require BASE_PATH .
                '/modules/Auth/Views/unauthorized.php';

            exit;
        }
    }
}