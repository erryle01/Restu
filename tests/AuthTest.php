<?php

declare(strict_types=1);

class AuthTest
{
    public static function register(TestRunner $runner): void
    {
        $runner->add(
            'Authentication',
            'Admin account exists',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->prepare("
                    SELECT id, username, password, status
                    FROM users
                    WHERE username = ?
                    LIMIT 1
                ");

                $stmt->execute([
                    'admin'
                ]);

                $user =
                    $stmt->fetch();

                TestCase::assertNotNull(
                    $user,
                    'Admin user does not exist.'
                );

                TestCase::assertEquals(
                    'admin',
                    $user['username']
                );
            }
        );

        $runner->add(
            'Authentication',
            'Admin account is active',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->prepare("
                    SELECT status
                    FROM users
                    WHERE username = ?
                    LIMIT 1
                ");

                $stmt->execute([
                    'admin'
                ]);

                $status =
                    $stmt->fetchColumn();

                TestCase::assertEquals(
                    'active',
                    $status,
                    'Admin account is not active.'
                );
            }
        );

        $runner->add(
            'Authentication',
            'Admin password is hashed',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->prepare("
                    SELECT password
                    FROM users
                    WHERE username = ?
                    LIMIT 1
                ");

                $stmt->execute([
                    'admin'
                ]);

                $password =
                    $stmt->fetchColumn();

                TestCase::assertTrue(
                    password_get_info($password)['algo'] !== 0,
                    'Password is not properly hashed.'
                );
            }
        );

        $runner->add(
            'Authentication',
            'Admin has a role',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->prepare("
                    SELECT COUNT(*)

                    FROM user_roles ur

                    INNER JOIN users u
                        ON u.id = ur.user_id

                    WHERE u.username = ?
                ");

                $stmt->execute([
                    'admin'
                ]);

                $count =
                    (int) $stmt->fetchColumn();

                TestCase::assertTrue(
                    $count > 0,
                    'Admin has no assigned role.'
                );
            }
        );

        $runner->add(
            'Authentication',
            'Admin has Super Admin role',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->prepare("
                    SELECT COUNT(*)

                    FROM user_roles ur

                    INNER JOIN users u
                        ON u.id = ur.user_id

                    INNER JOIN roles r
                        ON r.id = ur.role_id

                    WHERE u.username = ?
                    AND r.name = 'Super Admin'
                ");

                $stmt->execute([
                    'admin'
                ]);

                $count =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    1,
                    $count,
                    'Admin does not have Super Admin role.'
                );
            }
        );

        $runner->add(
            'Authentication',
            'Admin has dashboard permission',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->prepare("
                    SELECT COUNT(*)

                    FROM user_roles ur

                    INNER JOIN users u
                        ON u.id = ur.user_id

                    INNER JOIN role_permissions rp
                        ON rp.role_id = ur.role_id

                    INNER JOIN permissions p
                        ON p.id = rp.permission_id

                    WHERE u.username = ?
                    AND p.permission_key = 'dashboard.view'
                ");

                $stmt->execute([
                    'admin'
                ]);

                $count =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    1,
                    $count,
                    'Admin does not have dashboard.view.'
                );
            }
        );
    }
}