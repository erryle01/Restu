<?php

declare(strict_types=1);

class RBACTest
{
    public static function register(TestRunner $runner): void
    {
        $roles = [
            'Super Admin',
            'Admin',
            'Manager',
            'Cashier',
            'HR',
            'Accountant'
        ];

        foreach ($roles as $role) {

            $runner->add(
                'RBAC',
                "{$role} role exists",
                function () use ($role) {

                    $db =
                        Database::getInstance();

                    $stmt = $db->prepare("
                        SELECT COUNT(*)
                        FROM roles
                        WHERE name = ?
                    ");

                    $stmt->execute([
                        $role
                    ]);

                    $count =
                        (int) $stmt->fetchColumn();

                    TestCase::assertEquals(
                        1,
                        $count,
                        "Role '{$role}' does not exist."
                    );
                }
            );
        }

        $permissions = [
            'dashboard.view',
            'pos.view',
            'pos.create',
            'pos.edit',
            'pos.discount',
            'pos.void',
            'pos.refund',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'inventory.view',
            'inventory.adjust',
            'inventory.purchase',
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',
            'attendance.view',
            'attendance.manage',
            'payroll.view',
            'payroll.create',
            'payroll.calculate',
            'payroll.approve',
            'payroll.pay'
        ];

        foreach ($permissions as $permission) {

            $runner->add(
                'RBAC',
                "{$permission} permission exists",
                function () use ($permission) {

                    $db =
                        Database::getInstance();

                    $stmt = $db->prepare("
                        SELECT COUNT(*)
                        FROM permissions
                        WHERE permission_key = ?
                    ");

                    $stmt->execute([
                        $permission
                    ]);

                    $count =
                        (int) $stmt->fetchColumn();

                    TestCase::assertEquals(
                        1,
                        $count,
                        "Permission '{$permission}' does not exist."
                    );
                }
            );
        }

        $runner->add(
            'RBAC',
            'Super Admin has permissions',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM role_permissions rp

                    INNER JOIN roles r
                        ON r.id = rp.role_id

                    WHERE r.name = 'Super Admin'
                ");

                $count =
                    (int) $stmt->fetchColumn();

                TestCase::assertTrue(
                    $count > 0,
                    'Super Admin has no permissions.'
                );
            }
        );

        $runner->add(
            'RBAC',
            'Admin has Super Admin permissions',
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

                    INNER JOIN role_permissions rp
                        ON rp.role_id = r.id

                    WHERE u.username = ?
                    AND r.name = 'Super Admin'
                ");

                $stmt->execute([
                    'admin'
                ]);

                $count =
                    (int) $stmt->fetchColumn();

                TestCase::assertTrue(
                    $count > 0,
                    'Admin has no Super Admin permissions.'
                );
            }
        );
    }
}