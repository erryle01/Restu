<?php

declare(strict_types=1);

class EmployeeTest
{
    public static function register(TestRunner $runner): void
    {
        $runner->add(
            'Employees',
            'Employee table is readable',
            function () {

                $db =
                    Database::getInstance();

                $stmt =
                    $db->query("
                        SELECT COUNT(*)
                        FROM employees
                    ");

                $count =
                    $stmt->fetchColumn();

                TestCase::assertTrue(
                    is_numeric($count),
                    'Employees table cannot be read.'
                );
            }
        );

        $runner->add(
            'Employees',
            'Department relationship is valid',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM employees e

                    LEFT JOIN departments d
                        ON d.id = e.department_id

                    WHERE d.id IS NULL
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Employees contain invalid department references.'
                );
            }
        );

        $runner->add(
            'Employees',
            'Position relationship is valid',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM employees e

                    LEFT JOIN positions p
                        ON p.id = e.position_id

                    WHERE p.id IS NULL
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Employees contain invalid position references.'
                );
            }
        );

        $runner->add(
            'Employees',
            'Employee department matches position department',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM employees e

                    INNER JOIN positions p
                        ON p.id = e.position_id

                    WHERE e.department_id != p.department_id
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Employee has position from another department.'
                );
            }
        );

        $runner->add(
            'Employees',
            'Employee salary types are valid',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM employees

                    WHERE salary_type NOT IN
                    (
                        'monthly',
                        'daily',
                        'hourly'
                    )
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Invalid employee salary type found.'
                );
            }
        );

        $runner->add(
            'Employees',
            'Employee salaries are not negative',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)
                    FROM employees
                    WHERE basic_salary < 0
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Negative employee salary found.'
                );
            }
        );
    }
}