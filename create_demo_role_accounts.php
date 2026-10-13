<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (($argv[1] ?? '') !== '--create-demo-accounts') {
    fwrite(STDERR, "Usage: php create_demo_role_accounts.php --create-demo-accounts\n");
    exit(1);
}

require_once __DIR__ . '/config/config.php';
require_once BASE_PATH . '/core/Database.php';

$db = Database::getInstance();
$roles = $db->query('SELECT id, name FROM roles ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

if (!$roles) {
    fwrite(STDERR, "No roles exist. Create roles before generating demo accounts.\n");
    exit(1);
}

$created = [];
$db->beginTransaction();

try {
    foreach ($roles as $role) {
        $roleId = (int) $role['id'];
        $roleName = (string) $role['name'];
        $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '_', $roleName), '_'));
        $username = 'demo_' . substr($slug ?: 'role', 0, 24) . '_' . $roleId;

        $check = $db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $check->execute([$username]);
        if ($check->fetchColumn()) {
            continue;
        }

        $password = 'Demo!' . bin2hex(random_bytes(10));
        $email = $username . '@example.test';
        $insertUser = $db->prepare("INSERT INTO users (username, email, password, full_name, status) VALUES (?, ?, ?, ?, 'active')");
        $insertUser->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            'Demo ' . $roleName
        ]);
        $userId = (int) $db->lastInsertId();

        $assignRole = $db->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
        $assignRole->execute([$userId, $roleId]);

        $created[] = [
            'role' => $roleName,
            'username' => $username,
            'password' => $password
        ];
    }

    $db->commit();
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, 'Could not create demo accounts: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

if (!$created) {
    echo "Demo accounts already exist for all current roles; no changes made.\n";
    exit(0);
}

echo "DEVELOPMENT DEMO ACCOUNTS (save these credentials; passwords are not stored in plaintext)\n";
echo "Disable or delete these accounts before production deployment.\n\n";
foreach ($created as $account) {
    echo $account['role'] . "\t" . $account['username'] . "\t" . $account['password'] . PHP_EOL;
}
