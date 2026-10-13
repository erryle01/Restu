<?php

declare(strict_types=1);

class AttendanceTest
{
    public static function register(TestRunner $runner): void
    {
        $runner->add(
            'Attendance',
            'Attendance table is readable',
            function () {

                $db =
                    Database::getInstance();

                $stmt =
                    $db->query("
                        SELECT COUNT(*)
                        FROM attendance
                    ");

                $count =
                    $stmt->fetchColumn();

                TestCase::assertTrue(
                    is_numeric($count),
                    'Attendance table cannot be read.'
                );
            }
        );

        $runner->add(
            'Attendance',
            'Attendance employee references are valid',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM attendance a

                    LEFT JOIN employees e
                        ON e.id = a.employee_id

                    WHERE e.id IS NULL
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Invalid attendance employee references found.'
                );
            }
        );

        $runner->add(
            'Attendance',
            'Attendance statuses are valid',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM attendance

                    WHERE status NOT IN
                    (
                        'present',
                        'late',
                        'absent',
                        'half_day',
                        'leave',
                        'holiday',
                        'rest_day'
                    )
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Invalid attendance status found.'
                );
            }
        );

        $runner->add(
            'Attendance',
            'Attendance minutes are not negative',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM attendance

                    WHERE
                        break_minutes < 0
                        OR late_minutes < 0
                        OR undertime_minutes < 0
                        OR overtime_minutes < 0
                        OR total_minutes < 0
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Negative attendance minutes found.'
                );
            }
        );

        $runner->add(
            'Attendance',
            'Attendance actual time order is valid',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM attendance

                    WHERE time_in IS NOT NULL
                    AND time_out IS NOT NULL
                    AND time_out <= time_in
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Attendance contains Time Out <= Time In.'
                );
            }
        );

        $runner->add(
            'Attendance',
            'Total minutes are logically calculated',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM attendance

                    WHERE time_in IS NOT NULL
                    AND time_out IS NOT NULL

                    AND status NOT IN
                    (
                        'absent',
                        'leave',
                        'holiday',
                        'rest_day'
                    )

                    AND total_minutes >
                        TIMESTAMPDIFF(
                            MINUTE,
                            time_in,
                            time_out
                        )
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'total_minutes exceeds actual elapsed time.'
                );
            }
        );

        $runner->add(
            'Attendance',
            'Overtime does not exceed total minutes',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM attendance

                    WHERE overtime_minutes > total_minutes
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Overtime minutes exceed total worked minutes.'
                );
            }
        );

        $runner->add(
            'Attendance',
            'Scheduled fields are consistent',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM attendance

                    WHERE
                        scheduled_time_in IS NOT NULL
                        AND scheduled_in IS NOT NULL

                        AND scheduled_time_in != scheduled_in
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'scheduled_time_in and scheduled_in differ.'
                );
            }
        );

        $runner->add(
            'Attendance',
            'Scheduled out fields are consistent',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM attendance

                    WHERE
                        scheduled_time_out IS NOT NULL
                        AND scheduled_out IS NOT NULL

                        AND scheduled_time_out != scheduled_out
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'scheduled_time_out and scheduled_out differ.'
                );
            }
        );

        $runner->add(
            'Attendance',
            'Attendance remarks column works',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT remarks
                    FROM attendance
                    LIMIT 1
                ");

                $stmt->fetch();
            }
        );
    }
}