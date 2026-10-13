<?php

class View
{
    public static function render(
        string $view,
        array $data = []
    ): void {

        extract($data);

        $file = BASE_PATH . '/' . $view . '.php';

        if (!file_exists($file)) {
            die("View not found: " . $file);
        }

        require $file;
    }
}