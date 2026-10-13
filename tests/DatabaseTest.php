<?php

declare(strict_types=1);

class DatabaseTest
{
    public static function register(TestRunner $runner): void
    {
        $runner->add(
            'Database',
            'Database connection',
            function () {

                $db =
                    Database::getInstance();

                TestCase::assertTrue(
                    $db instanceof PDO,
                    'Database connection failed.'
                );
            }
        );

        $tables = [
            'users',
            'roles',
            'permissions',
            'user_roles',
            'role_permissions',
            'login_attempts',
            'settings',
            'activity_logs',
            'departments',
            'positions',
            'employees',
            'employee_schedules',
            'attendance',
            'products',
            'inventory_movements',
            'restaurant_tables',
            'orders',
            'order_items'
        ];

        foreach ($tables as $table) {

            $runner->add(
                'Database',
                "{$table} table exists",
                function () use ($table) {

                    $db =
                        Database::getInstance();

                    TestCase::assertHasTable(
                        $db,
                        $table
                    );
                }
            );
        }

        $attendanceColumns = [
            'employee_id',
            'attendance_date',
            'scheduled_time_in',
            'scheduled_time_out',
            'time_in',
            'time_out',
            'break_minutes',
            'scheduled_in',
            'scheduled_out',
            'late_minutes',
            'undertime_minutes',
            'overtime_minutes',
            'total_minutes',
            'status',
            'remarks'
        ];

        foreach ($attendanceColumns as $column) {

            $runner->add(
                'Database',
                "attendance.{$column} exists",
                function () use ($column) {

                    $db =
                        Database::getInstance();

                    TestCase::assertHasColumn(
                        $db,
                        'attendance',
                        $column
                    );
                }
            );
        }

        $employeeColumns = [
            'employee_code',
            'first_name',
            'last_name',
            'department_id',
            'position_id',
            'hire_date',
            'employment_status',
            'salary_type',
            'basic_salary'
        ];

        foreach ($employeeColumns as $column) {

            $runner->add(
                'Database',
                "employees.{$column} exists",
                function () use ($column) {

                    $db =
                        Database::getInstance();

                    TestCase::assertHasColumn(
                        $db,
                        'employees',
                        $column
                    );
                }
            );
        }

        $scheduleColumns = [
            'employee_id',
            'day_of_week',
            'time_in',
            'time_out',
            'break_minutes',
            'is_rest_day'
        ];

        foreach ($scheduleColumns as $column) {

            $runner->add(
                'Database',
                "employee_schedules.{$column} exists",
                function () use ($column) {

                    $db =
                        Database::getInstance();

                    TestCase::assertHasColumn(
                        $db,
                        'employee_schedules',
                        $column
                    );
                }
            );
        }
    }
}