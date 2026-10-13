<?php

require_once __DIR__ . '/config/config.php';

require_once BASE_PATH . '/core/Database.php';

$password = password_hash(
    'admin123',
    PASSWORD_DEFAULT
);

$db = Database::getInstance();

$stmt = $db->prepare("
    INSERT INTO users
    (
        username,
        email,
        password,
        full_name,
        status
    )
    VALUES
    (
        :username,
        :email,
        :password,
        :full_name,
        'active'
    )
");

$stmt->execute([
    'username'  => 'admin',
    'email'     => 'admin@example.com',
    'password'  => $password,
    'full_name' => 'System Administrator'
]);

echo "Admin created successfully.";