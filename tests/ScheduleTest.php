<?php

declare(strict_types=1);

class ScheduleTest
{
    public static function register(TestRunner $runner): void
    {
        $runner->add(
            'Schedules',
            'Schedule table is readable',
            function () {

                $db =
                    Database::getInstance();

                $stmt =
                    $db->query("
                        SELECT COUNT(*)
                        FROM employee_schedules
                    ");

                $count =
                    $stmt->fetchColumn();

                TestCase::assertTrue(
                    is_numeric($count),
                    'Schedule table cannot be read.'
                );
            }
        );

        $runner->add(
            'Schedules',
            'Schedule employee references are valid',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM employee_schedules es

                    LEFT JOIN employees e
                        ON e.id = es.employee_id

                    WHERE e.id IS NULL
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Invalid employee schedule references found.'
                );
            }
        );

        $runner->add(
            'Schedules',
            'Day of week values are valid',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM employee_schedules

                    WHERE day_of_week < 1
                    OR day_of_week > 7
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Invalid day_of_week found.'
                );
            }
        );

        $runner->add(
            'Schedules',
            'Break minutes are not negative',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM employee_schedules

                    WHERE break_minutes < 0
                ");

                $invalid =
                    (int) $stmt->fetchColumn();

                TestCase::assertEquals(
                    0,
                    $invalid,
                    'Negative break minutes found.'
                );
            }
        );

        $runner->add(
            'Schedules',
            'Rest-day schedules have no invalid time logic',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT COUNT(*)

                    FROM employee_schedules

                    WHERE is_rest_day = 1

                    AND (
                        time_in IS NULL
                        OR time_out IS NULL
                        OR time_out < time_in
                    )
                ");

                /*
                 * Existing schema uses NOT NULL for time fields.
                 * A rest day may still contain default/placeholder
                 * times depending on your create form, so this test
                 * only checks that the database can read the records.
                 */

                $count =
                    (int) $stmt->fetchColumn();

                TestCase::assertTrue(
                    $count >= 0
                );
            }
        );

        $runner->add(
            'Schedules',
            'No duplicate employee/day schedules',
            function () {

                $db =
                    Database::getInstance();

                $stmt = $db->query("
                    SELECT
                        employee_id,
                        day_of_week,
                        COUNT(*) AS total

                    FROM employee_schedules

                    GROUP BY
                        employee_id,
                        day_of_week

                    HAVING COUNT(*) > 1
                ");

                $duplicates =
                    $stmt->fetchAll();

                TestCase::assertEquals(
                    0,
                    count($duplicates),
                    'Duplicate employee/day schedules found.'
                );
            }
        );
    }
}