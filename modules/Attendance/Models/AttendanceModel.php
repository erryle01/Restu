<?php

class AttendanceModel extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    public function getEmployees(): array
    {
        $stmt = $this->db->query("
            SELECT
                e.id,
                e.employee_code,
                e.first_name,
                e.middle_name,
                e.last_name,
                e.department_id,
                e.position_id,
                e.employment_status,

                d.name AS department_name,
                p.name AS position_name

            FROM employees e

            LEFT JOIN departments d
                ON d.id = e.department_id

            LEFT JOIN positions p
                ON p.id = e.position_id

            WHERE e.employment_status != 'terminated'

            ORDER BY
                e.last_name ASC,
                e.first_name ASC
        ");

        return $stmt->fetchAll();
    }


    /*
    |--------------------------------------------------------------------------
    | Single Employee
    |--------------------------------------------------------------------------
    */

    public function getEmployee(
        int $employeeId
    ): ?array {

        $stmt = $this->db->prepare("
            SELECT
                e.id,
                e.employee_code,
                e.first_name,
                e.middle_name,
                e.last_name,
                e.email,
                e.phone,
                e.address,
                e.department_id,
                e.position_id,
                e.hire_date,
                e.employment_status,
                e.salary_type,
                e.basic_salary,

                d.name AS department_name,
                p.name AS position_name

            FROM employees e

            LEFT JOIN departments d
                ON d.id = e.department_id

            LEFT JOIN positions p
                ON p.id = e.position_id

            WHERE e.id = ?

            LIMIT 1
        ");

        $stmt->execute([
            $employeeId
        ]);

        $employee = $stmt->fetch();

        return $employee ?: null;
    }

    public function getEmployeeForUser(
        int $userId
    ): ?array {
        $stmt = $this->db->prepare(" 
            SELECT e.*
            FROM users u
            INNER JOIN employees e
                ON LOWER(TRIM(u.email)) = LOWER(TRIM(e.email))
            WHERE u.id = ?
              AND e.employment_status != 'terminated'
            LIMIT 1
        ");

        $stmt->execute([$userId]);
        $employee = $stmt->fetch();

        return $employee ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Find Attendance
    |--------------------------------------------------------------------------
    */

    public function find(
        int $id
    ): ?array {

        $stmt = $this->db->prepare("
            SELECT
                a.id,
                a.employee_id,
                a.attendance_date,

                a.scheduled_time_in,
                a.scheduled_time_out,

                a.time_in,
                a.time_out,

                a.break_minutes,

                a.scheduled_in,
                a.scheduled_out,

                a.late_minutes,
                a.undertime_minutes,
                a.overtime_minutes,
                a.total_minutes,

                /*
                 * Derived values.
                 *
                 * These are NOT stored in the database.
                 */
                GREATEST(
                    a.total_minutes - a.overtime_minutes,
                    0
                ) AS regular_minutes,

                ROUND(
                    GREATEST(
                        a.total_minutes - a.overtime_minutes,
                        0
                    ) / 60,
                    2
                ) AS regular_hours,

                ROUND(
                    a.overtime_minutes / 60,
                    2
                ) AS overtime_hours,

                a.status,
                a.remarks,

                a.created_at,
                a.updated_at,

                e.employee_code,
                e.first_name,
                e.middle_name,
                e.last_name,

                d.name AS department_name,
                p.name AS position_name

            FROM attendance a

            INNER JOIN employees e
                ON e.id = a.employee_id

            LEFT JOIN departments d
                ON d.id = e.department_id

            LEFT JOIN positions p
                ON p.id = e.position_id

            WHERE a.id = ?

            LIMIT 1
        ");

        $stmt->execute([
            $id
        ]);

        $attendance = $stmt->fetch();

        return $attendance ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Find By Employee + Date
    |--------------------------------------------------------------------------
    */

    public function findByEmployeeDate(
        int $employeeId,
        string $attendanceDate,
        ?int $excludeId = null
    ): ?array {

        if ($excludeId !== null) {

            $stmt = $this->db->prepare("
                SELECT
                    *
                FROM attendance

                WHERE employee_id = ?
                AND attendance_date = ?
                AND id != ?

                LIMIT 1
            ");

            $stmt->execute([
                $employeeId,
                $attendanceDate,
                $excludeId
            ]);

        } else {

            $stmt = $this->db->prepare("
                SELECT
                    *
                FROM attendance

                WHERE employee_id = ?
                AND attendance_date = ?

                LIMIT 1
            ");

            $stmt->execute([
                $employeeId,
                $attendanceDate
            ]);
        }

        $attendance = $stmt->fetch();

        return $attendance ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Get All Attendance
    |--------------------------------------------------------------------------
    */

    public function getAll(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $employeeId = null,
        ?string $status = null,
        int $page = 1,
        int $perPage = 20
    ): array {

        $where = [];
        $params = [];

        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if (
            $dateFrom !== null &&
            $dateFrom !== ''
        ) {

            $where[] =
                "a.attendance_date >= ?";

            $params[] =
                $dateFrom;
        }

        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if (
            $dateTo !== null &&
            $dateTo !== ''
        ) {

            $where[] =
                "a.attendance_date <= ?";

            $params[] =
                $dateTo;
        }

        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */

        if (
            $employeeId !== null &&
            $employeeId > 0
        ) {

            $where[] =
                "a.employee_id = ?";

            $params[] =
                $employeeId;
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [
            'present',
            'late',
            'absent',
            'half_day',
            'leave',
            'holiday',
            'rest_day'
        ];

        if (
            $status !== null &&
            in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {

            $where[] =
                "a.status = ?";

            $params[] =
                $status;
        }

        /*
        |--------------------------------------------------------------------------
        | WHERE
        |--------------------------------------------------------------------------
        */

        $whereSql = '';

        if (!empty($where)) {

            $whereSql =
                'WHERE ' .
                implode(
                    ' AND ',
                    $where
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $page =
            max(
                1,
                $page
            );

        $perPage =
            max(
                1,
                min(
                    100,
                    $perPage
                )
            );

        $offset =
            ($page - 1) *
            $perPage;

        /*
        |--------------------------------------------------------------------------
        | Query
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT
                a.id,
                a.employee_id,
                a.attendance_date,

                a.scheduled_time_in,
                a.scheduled_time_out,

                a.time_in,
                a.time_out,

                a.break_minutes,

                a.scheduled_in,
                a.scheduled_out,

                a.late_minutes,
                a.undertime_minutes,
                a.overtime_minutes,
                a.total_minutes,

                /*
                 * Regular time is derived.
                 *
                 * total_minutes
                 * -
                 * overtime_minutes
                 */
                GREATEST(
                    a.total_minutes - a.overtime_minutes,
                    0
                ) AS regular_minutes,

                /*
                 * Regular hours.
                 */
                ROUND(
                    GREATEST(
                        a.total_minutes - a.overtime_minutes,
                        0
                    ) / 60,
                    2
                ) AS regular_hours,

                /*
                 * Overtime hours.
                 */
                ROUND(
                    a.overtime_minutes / 60,
                    2
                ) AS overtime_hours,

                a.status,
                a.remarks,

                a.created_at,
                a.updated_at,

                e.employee_code,
                e.first_name,
                e.middle_name,
                e.last_name,

                d.name AS department_name,
                p.name AS position_name

            FROM attendance a

            INNER JOIN employees e
                ON e.id = a.employee_id

            LEFT JOIN departments d
                ON d.id = e.department_id

            LEFT JOIN positions p
                ON p.id = e.position_id

            {$whereSql}

            ORDER BY
                a.attendance_date DESC,
                e.last_name ASC,
                e.first_name ASC

            LIMIT {$perPage}
            OFFSET {$offset}
        ";

        $stmt =
            $this->db->prepare(
                $sql
            );

        $stmt->execute(
            $params
        );

        return $stmt->fetchAll();
    }


    /*
    |--------------------------------------------------------------------------
    | Count Attendance
    |--------------------------------------------------------------------------
    */

    public function countAll(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $employeeId = null,
        ?string $status = null
    ): int {

        $where = [];
        $params = [];

        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if (
            $dateFrom !== null &&
            $dateFrom !== ''
        ) {

            $where[] =
                "attendance_date >= ?";

            $params[] =
                $dateFrom;
        }

        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if (
            $dateTo !== null &&
            $dateTo !== ''
        ) {

            $where[] =
                "attendance_date <= ?";

            $params[] =
                $dateTo;
        }

        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */

        if (
            $employeeId !== null &&
            $employeeId > 0
        ) {

            $where[] =
                "employee_id = ?";

            $params[] =
                $employeeId;
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [
            'present',
            'late',
            'absent',
            'half_day',
            'leave',
            'holiday',
            'rest_day'
        ];

        if (
            $status !== null &&
            in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {

            $where[] =
                "status = ?";

            $params[] =
                $status;
        }

        /*
        |--------------------------------------------------------------------------
        | WHERE
        |--------------------------------------------------------------------------
        */

        $whereSql = '';

        if (!empty($where)) {

            $whereSql =
                'WHERE ' .
                implode(
                    ' AND ',
                    $where
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Count
        |--------------------------------------------------------------------------
        */

        $stmt =
            $this->db->prepare("
                SELECT COUNT(*)
                FROM attendance
                {$whereSql}
            ");

        $stmt->execute(
            $params
        );

        return (int)
            $stmt->fetchColumn();
    }


    /*
    |--------------------------------------------------------------------------
    | Create Attendance
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data
    ): int {

        $stmt = $this->db->prepare("
            INSERT INTO attendance
            (
                employee_id,
                attendance_date,

                scheduled_time_in,
                scheduled_time_out,

                time_in,
                time_out,

                break_minutes,

                scheduled_in,
                scheduled_out,

                late_minutes,
                undertime_minutes,
                overtime_minutes,
                total_minutes,

                status,
                remarks
            )

            VALUES
            (
                ?,
                ?,

                ?,
                ?,

                ?,
                ?,

                ?,

                ?,
                ?,

                ?,
                ?,
                ?,
                ?,

                ?,
                ?
            )
        ");

        /*
        |--------------------------------------------------------------------------
        | Scheduled Time
        |--------------------------------------------------------------------------
        |
        | The database currently contains:
        |
        | scheduled_time_in
        | scheduled_time_out
        |
        | AND:
        |
        | scheduled_in
        | scheduled_out
        |
        | Keep both synchronized.
        |
        */

        $scheduledTimeIn =
            $data['scheduled_time_in']
            ?? $data['scheduled_in']
            ?? null;

        $scheduledTimeOut =
            $data['scheduled_time_out']
            ?? $data['scheduled_out']
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | Execute
        |--------------------------------------------------------------------------
        */

        $stmt->execute([
            $data['employee_id'],

            $data['attendance_date'],

            $scheduledTimeIn,
            $scheduledTimeOut,

            $data['time_in']
                ?? null,

            $data['time_out']
                ?? null,

            (int) (
                $data['break_minutes']
                ?? 0
            ),

            $scheduledTimeIn,
            $scheduledTimeOut,

            (int) (
                $data['late_minutes']
                ?? 0
            ),

            (int) (
                $data['undertime_minutes']
                ?? 0
            ),

            (int) (
                $data['overtime_minutes']
                ?? 0
            ),

            (int) (
                $data['total_minutes']
                ?? 0
            ),

            $data['status']
                ?? 'present',

            $data['remarks']
                ?? null
        ]);

        return (int)
            $this->db->lastInsertId();
    }


    /*
    |--------------------------------------------------------------------------
    | Update Attendance
    |--------------------------------------------------------------------------
    */

    public function update(
        int $id,
        array $data
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE attendance

            SET
                employee_id = ?,
                attendance_date = ?,

                scheduled_time_in = ?,
                scheduled_time_out = ?,

                time_in = ?,
                time_out = ?,

                break_minutes = ?,

                scheduled_in = ?,
                scheduled_out = ?,

                late_minutes = ?,
                undertime_minutes = ?,
                overtime_minutes = ?,
                total_minutes = ?,

                status = ?,
                remarks = ?

            WHERE id = ?
        ");

        /*
        |--------------------------------------------------------------------------
        | Scheduled Time
        |--------------------------------------------------------------------------
        */

        $scheduledTimeIn =
            $data['scheduled_time_in']
            ?? $data['scheduled_in']
            ?? null;

        $scheduledTimeOut =
            $data['scheduled_time_out']
            ?? $data['scheduled_out']
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        return $stmt->execute([
            $data['employee_id'],

            $data['attendance_date'],

            $scheduledTimeIn,
            $scheduledTimeOut,

            $data['time_in']
                ?? null,

            $data['time_out']
                ?? null,

            (int) (
                $data['break_minutes']
                ?? 0
            ),

            $scheduledTimeIn,
            $scheduledTimeOut,

            (int) (
                $data['late_minutes']
                ?? 0
            ),

            (int) (
                $data['undertime_minutes']
                ?? 0
            ),

            (int) (
                $data['overtime_minutes']
                ?? 0
            ),

            (int) (
                $data['total_minutes']
                ?? 0
            ),

            $data['status']
                ?? 'present',

            $data['remarks']
                ?? null,

            $id
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Attendance
    |--------------------------------------------------------------------------
    */

    public function delete(
        int $id
    ): bool {

        $stmt =
            $this->db->prepare("
                DELETE FROM attendance
                WHERE id = ?
            ");

        return $stmt->execute([
            $id
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Employee Attendance
    |--------------------------------------------------------------------------
    */

    public function getByEmployee(
        int $employeeId,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {

        $where = [
            'a.employee_id = ?'
        ];

        $params = [
            $employeeId
        ];

        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if (
            $dateFrom !== null &&
            $dateFrom !== ''
        ) {

            $where[] =
                'a.attendance_date >= ?';

            $params[] =
                $dateFrom;
        }

        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if (
            $dateTo !== null &&
            $dateTo !== ''
        ) {

            $where[] =
                'a.attendance_date <= ?';

            $params[] =
                $dateTo;
        }

        /*
        |--------------------------------------------------------------------------
        | WHERE
        |--------------------------------------------------------------------------
        */

        $whereSql =
            'WHERE ' .
            implode(
                ' AND ',
                $where
            );

        /*
        |--------------------------------------------------------------------------
        | Query
        |--------------------------------------------------------------------------
        */

        $stmt = $this->db->prepare("
            SELECT
                a.id,
                a.employee_id,
                a.attendance_date,

                a.scheduled_time_in,
                a.scheduled_time_out,

                a.time_in,
                a.time_out,

                a.break_minutes,

                a.scheduled_in,
                a.scheduled_out,

                a.late_minutes,
                a.undertime_minutes,
                a.overtime_minutes,
                a.total_minutes,

                /*
                 * Derived regular minutes.
                 */
                GREATEST(
                    a.total_minutes - a.overtime_minutes,
                    0
                ) AS regular_minutes,

                /*
                 * Derived regular hours.
                 */
                ROUND(
                    GREATEST(
                        a.total_minutes - a.overtime_minutes,
                        0
                    ) / 60,
                    2
                ) AS regular_hours,

                /*
                 * Derived overtime hours.
                 */
                ROUND(
                    a.overtime_minutes / 60,
                    2
                ) AS overtime_hours,

                a.status,
                a.remarks,

                a.created_at,
                a.updated_at

            FROM attendance a

            {$whereSql}

            ORDER BY
                a.attendance_date DESC,
                a.id DESC
        ");

        $stmt->execute(
            $params
        );

        return $stmt->fetchAll();
    }


    /*
    |--------------------------------------------------------------------------
    | Schedule For Employee + Date
    |--------------------------------------------------------------------------
    */

    public function getScheduleForEmployeeDate(
        int $employeeId,
        string $attendanceDate
    ): ?array {

        /*
        |--------------------------------------------------------------------------
        | PHP date N:
        |--------------------------------------------------------------------------
        |
        | 1 = Monday
        | 2 = Tuesday
        | 3 = Wednesday
        | 4 = Thursday
        | 5 = Friday
        | 6 = Saturday
        | 7 = Sunday
        |
        */

        $timestamp =
            strtotime(
                $attendanceDate
            );

        if ($timestamp === false) {
            return null;
        }

        $dayOfWeek =
            (int) date(
                'N',
                $timestamp
            );

        $stmt = $this->db->prepare("
            SELECT
                id,
                employee_id,
                day_of_week,
                time_in,
                time_out,
                break_minutes,
                is_rest_day,
                created_at

            FROM employee_schedules

            WHERE employee_id = ?
            AND day_of_week = ?

            LIMIT 1
        ");

        $stmt->execute([
            $employeeId,
            $dayOfWeek
        ]);

        $schedule =
            $stmt->fetch();

        return $schedule ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Employee Summary
    |--------------------------------------------------------------------------
    */

    public function getEmployeeSummary(
        int $employeeId,
        string $dateFrom,
        string $dateTo
    ): array {

        $stmt = $this->db->prepare("
            SELECT

                COUNT(*) AS total_days,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'present'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS present_days,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'late'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS late_days,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'absent'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS absent_days,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'half_day'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS half_days,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'leave'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS leave_days,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'holiday'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS holiday_days,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'rest_day'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS rest_days,

                /*
                |--------------------------------------------------------------------------
                | Total Worked Minutes
                |--------------------------------------------------------------------------
                */

                COALESCE(
                    SUM(total_minutes),
                    0
                ) AS total_minutes,

                /*
                |--------------------------------------------------------------------------
                | Regular Minutes
                |--------------------------------------------------------------------------
                */

                COALESCE(
                    SUM(
                        GREATEST(
                            total_minutes -
                            overtime_minutes,
                            0
                        )
                    ),
                    0
                ) AS regular_minutes,

                /*
                |--------------------------------------------------------------------------
                | Regular Hours
                |--------------------------------------------------------------------------
                */

                ROUND(
                    COALESCE(
                        SUM(
                            GREATEST(
                                total_minutes -
                                overtime_minutes,
                                0
                            )
                        ),
                        0
                    ) / 60,
                    2
                ) AS regular_hours,

                /*
                |--------------------------------------------------------------------------
                | Overtime Minutes
                |--------------------------------------------------------------------------
                */

                COALESCE(
                    SUM(overtime_minutes),
                    0
                ) AS overtime_minutes,

                /*
                |--------------------------------------------------------------------------
                | Overtime Hours
                |--------------------------------------------------------------------------
                */

                ROUND(
                    COALESCE(
                        SUM(overtime_minutes),
                        0
                    ) / 60,
                    2
                ) AS overtime_hours,

                /*
                |--------------------------------------------------------------------------
                | Late Minutes
                |--------------------------------------------------------------------------
                */

                COALESCE(
                    SUM(late_minutes),
                    0
                ) AS late_minutes,

                /*
                |--------------------------------------------------------------------------
                | Undertime Minutes
                |--------------------------------------------------------------------------
                */

                COALESCE(
                    SUM(undertime_minutes),
                    0
                ) AS undertime_minutes

            FROM attendance

            WHERE employee_id = ?

            AND attendance_date >= ?

            AND attendance_date <= ?
        ");

        $stmt->execute([
            $employeeId,
            $dateFrom,
            $dateTo
        ]);

        $summary =
            $stmt->fetch();

        /*
        |--------------------------------------------------------------------------
        | Default Summary
        |--------------------------------------------------------------------------
        */

        if (!$summary) {

            return [
                'total_days' => 0,

                'present_days' => 0,
                'late_days' => 0,
                'absent_days' => 0,
                'half_days' => 0,
                'leave_days' => 0,
                'holiday_days' => 0,
                'rest_days' => 0,

                'total_minutes' => 0,
                'regular_minutes' => 0,
                'regular_hours' => 0,

                'overtime_minutes' => 0,
                'overtime_hours' => 0,

                'late_minutes' => 0,
                'undertime_minutes' => 0
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Convert Numeric Values
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                'total_days',

                'present_days',
                'late_days',
                'absent_days',
                'half_days',
                'leave_days',
                'holiday_days',
                'rest_days',

                'total_minutes',
                'regular_minutes',

                'overtime_minutes',

                'late_minutes',
                'undertime_minutes'
            ] as $key
        ) {

            $summary[$key] =
                (int) (
                    $summary[$key]
                    ?? 0
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Hours
        |--------------------------------------------------------------------------
        */

        $summary['regular_hours'] =
            round(
                (float) (
                    $summary['regular_hours']
                    ?? 0
                ),
                2
            );

        $summary['overtime_hours'] =
            round(
                (float) (
                    $summary['overtime_hours']
                    ?? 0
                ),
                2
            );

        return $summary;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Today's Attendance
    |--------------------------------------------------------------------------
    */

    public function getToday(
        ?int $employeeId = null
    ): array {

        $where = [
            'a.attendance_date = ?'
        ];

        $params = [
            date('Y-m-d')
        ];

        if (
            $employeeId !== null &&
            $employeeId > 0
        ) {

            $where[] =
                'a.employee_id = ?';

            $params[] =
                $employeeId;
        }

        $whereSql =
            'WHERE ' .
            implode(
                ' AND ',
                $where
            );

        $stmt = $this->db->prepare("
            SELECT
                a.*,

                /*
                 * Derived regular minutes.
                 */
                GREATEST(
                    a.total_minutes - a.overtime_minutes,
                    0
                ) AS regular_minutes,

                /*
                 * Derived regular hours.
                 */
                ROUND(
                    GREATEST(
                        a.total_minutes - a.overtime_minutes,
                        0
                    ) / 60,
                    2
                ) AS regular_hours,

                /*
                 * Derived overtime hours.
                 */
                ROUND(
                    a.overtime_minutes / 60,
                    2
                ) AS overtime_hours,

                e.employee_code,
                e.first_name,
                e.middle_name,
                e.last_name,

                d.name AS department_name,
                p.name AS position_name

            FROM attendance a

            INNER JOIN employees e
                ON e.id = a.employee_id

            LEFT JOIN departments d
                ON d.id = e.department_id

            LEFT JOIN positions p
                ON p.id = e.position_id

            {$whereSql}

            ORDER BY
                e.last_name ASC,
                e.first_name ASC
        ");

        $stmt->execute(
            $params
        );

        return $stmt->fetchAll();
    }


    /*
    |--------------------------------------------------------------------------
    | Attendance Statistics
    |--------------------------------------------------------------------------
    */

    public function getStatistics(
        string $dateFrom,
        string $dateTo
    ): array {

        $stmt = $this->db->prepare("
            SELECT

                COUNT(*) AS total_records,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'present'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS present,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'late'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS late,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'absent'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS absent,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'half_day'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS half_day,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'leave'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS leave_count,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'holiday'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS holiday,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'rest_day'
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS rest_day,

                /*
                |--------------------------------------------------------------------------
                | Total Minutes
                |--------------------------------------------------------------------------
                */

                COALESCE(
                    SUM(total_minutes),
                    0
                ) AS total_minutes,

                /*
                |--------------------------------------------------------------------------
                | Regular Minutes
                |--------------------------------------------------------------------------
                */

                COALESCE(
                    SUM(
                        GREATEST(
                            total_minutes -
                            overtime_minutes,
                            0
                        )
                    ),
                    0
                ) AS regular_minutes,

                /*
                |--------------------------------------------------------------------------
                | Regular Hours
                |--------------------------------------------------------------------------
                */

                ROUND(
                    COALESCE(
                        SUM(
                            GREATEST(
                                total_minutes -
                                overtime_minutes,
                                0
                            )
                        ),
                        0
                    ) / 60,
                    2
                ) AS regular_hours,

                /*
                |--------------------------------------------------------------------------
                | Overtime Minutes
                |--------------------------------------------------------------------------
                */

                COALESCE(
                    SUM(overtime_minutes),
                    0
                ) AS overtime_minutes,

                /*
                |--------------------------------------------------------------------------
                | Overtime Hours
                |--------------------------------------------------------------------------
                */

                ROUND(
                    COALESCE(
                        SUM(overtime_minutes),
                        0
                    ) / 60,
                    2
                ) AS overtime_hours

            FROM attendance

            WHERE attendance_date >= ?

            AND attendance_date <= ?
        ");

        $stmt->execute([
            $dateFrom,
            $dateTo
        ]);

        $result =
            $stmt->fetch();

        if (!$result) {

            return [
                'total_records' => 0,

                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'half_day' => 0,
                'leave_count' => 0,
                'holiday' => 0,
                'rest_day' => 0,

                'total_minutes' => 0,

                'regular_minutes' => 0,
                'regular_hours' => 0,

                'overtime_minutes' => 0,
                'overtime_hours' => 0
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Integer Values
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                'total_records',

                'present',
                'late',
                'absent',
                'half_day',
                'leave_count',
                'holiday',
                'rest_day',

                'total_minutes',
                'regular_minutes',

                'overtime_minutes'
            ] as $key
        ) {

            $result[$key] =
                (int) (
                    $result[$key]
                    ?? 0
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Hour Values
        |--------------------------------------------------------------------------
        */

        $result['regular_hours'] =
            round(
                (float) (
                    $result['regular_hours']
                    ?? 0
                ),
                2
            );

        $result['overtime_hours'] =
            round(
                (float) (
                    $result['overtime_hours']
                    ?? 0
                ),
                2
            );

        return $result;
    }
}