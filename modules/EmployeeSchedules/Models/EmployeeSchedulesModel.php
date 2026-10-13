<?php

class EmployeeSchedulesModel extends Model
{
    /**
     * Get all employees.
     */
    public function getEmployees(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                employee_code,
                first_name,
                middle_name,
                last_name,
                employment_status
            FROM employees
            ORDER BY last_name ASC, first_name ASC
        ");

        return $stmt->fetchAll();
    }


    /**
     * Get one employee.
     */
    public function getEmployee(int $employeeId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                employee_code,
                first_name,
                middle_name,
                last_name,
                employment_status
            FROM employees
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$employeeId]);

        $employee = $stmt->fetch();

        return $employee ?: null;
    }


    /**
     * Get all schedules.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT
                es.id,
                es.employee_id,
                es.day_of_week,
                es.time_in,
                es.time_out,
                es.break_minutes,
                es.is_rest_day,
                es.created_at,

                e.employee_code,

                CONCAT(
                    e.first_name,
                    ' ',
                    CASE
                        WHEN e.middle_name IS NOT NULL
                        AND e.middle_name != ''
                        THEN CONCAT(e.middle_name, ' ')
                        ELSE ''
                    END,
                    e.last_name
                ) AS employee_name

            FROM employee_schedules es

            INNER JOIN employees e
                ON e.id = es.employee_id

            ORDER BY
                e.last_name ASC,
                e.first_name ASC,
                es.day_of_week ASC
        ");

        return $stmt->fetchAll();
    }


    /**
     * Get schedules for an employee.
     */
    public function getByEmployee(int $employeeId): array
    {
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
            ORDER BY day_of_week ASC
        ");

        $stmt->execute([$employeeId]);

        return $stmt->fetchAll();
    }


    /**
     * Find schedule by ID.
     */
    public function find(int $id): ?array
    {
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
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $schedule = $stmt->fetch();

        return $schedule ?: null;
    }


    /**
     * Find schedule for employee + day.
     */
    public function findByEmployeeDay(
        int $employeeId,
        int $dayOfWeek,
        ?int $excludeId = null
    ): ?array {

        if ($excludeId !== null) {

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
                AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $employeeId,
                $dayOfWeek,
                $excludeId
            ]);

        } else {

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
        }

        $schedule = $stmt->fetch();

        return $schedule ?: null;
    }


    /**
     * Create schedule.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO employee_schedules (
                employee_id,
                day_of_week,
                time_in,
                time_out,
                break_minutes,
                is_rest_day
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['employee_id'],
            $data['day_of_week'],
            $data['time_in'],
            $data['time_out'],
            $data['break_minutes'],
            $data['is_rest_day']
        ]);

        return (int) $this->db->lastInsertId();
    }


    /**
     * Update schedule.
     */
    public function update(
        int $id,
        array $data
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE employee_schedules
            SET
                employee_id = ?,
                day_of_week = ?,
                time_in = ?,
                time_out = ?,
                break_minutes = ?,
                is_rest_day = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['employee_id'],
            $data['day_of_week'],
            $data['time_in'],
            $data['time_out'],
            $data['break_minutes'],
            $data['is_rest_day'],
            $id
        ]);
    }


    /**
     * Delete schedule.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM employee_schedules
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }


    /**
     * Delete all schedules for an employee.
     */
    public function deleteByEmployee(
        int $employeeId
    ): bool {

        $stmt = $this->db->prepare("
            DELETE FROM employee_schedules
            WHERE employee_id = ?
        ");

        return $stmt->execute([$employeeId]);
    }


    public function getEmployeesWithScheduleSummary(
    ?string $search = null,
    ?int $departmentId = null,
    ?string $status = null,
    int $page = 1,
    int $perPage = 20
): array {
    $offset = ($page - 1) * $perPage;

    $where = [];
    $params = [];

    if ($search !== null && trim($search) !== '') {
        $where[] = "(
            e.employee_code LIKE ?
            OR e.first_name LIKE ?
            OR e.middle_name LIKE ?
            OR e.last_name LIKE ?
        )";

        $searchValue = '%' . trim($search) . '%';

        $params[] = $searchValue;
        $params[] = $searchValue;
        $params[] = $searchValue;
        $params[] = $searchValue;
    }

    if ($departmentId !== null && $departmentId > 0) {
        $where[] = "e.department_id = ?";
        $params[] = $departmentId;
    }

    if ($status !== null && in_array(
        $status,
        ['active', 'inactive', 'on_leave', 'terminated'],
        true
    )) {
        $where[] = "e.employment_status = ?";
        $params[] = $status;
    }

    $whereSql = '';

    if (!empty($where)) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    $sql = "
        SELECT
            e.id,
            e.employee_code,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.email,
            e.phone,
            e.department_id,
            e.employment_status,

            d.name AS department_name,

            COUNT(es.id) AS schedule_count,

            SUM(
                CASE
                    WHEN es.is_rest_day = 0 THEN 1
                    ELSE 0
                END
            ) AS working_days,

            SUM(
                CASE
                    WHEN es.is_rest_day = 1 THEN 1
                    ELSE 0
                END
            ) AS rest_days

        FROM employees e

        LEFT JOIN departments d
            ON d.id = e.department_id

        LEFT JOIN employee_schedules es
            ON es.employee_id = e.id

        {$whereSql}

        GROUP BY
            e.id,
            e.employee_code,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.email,
            e.phone,
            e.department_id,
            e.employment_status,
            d.name

        ORDER BY
            e.last_name ASC,
            e.first_name ASC

        LIMIT {$perPage}
        OFFSET {$offset}
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}


public function countEmployeesWithScheduleFilters(
    ?string $search = null,
    ?int $departmentId = null,
    ?string $status = null
): int {
    $where = [];
    $params = [];

    if ($search !== null && trim($search) !== '') {
        $where[] = "(
            e.employee_code LIKE ?
            OR e.first_name LIKE ?
            OR e.middle_name LIKE ?
            OR e.last_name LIKE ?
        )";

        $searchValue = '%' . trim($search) . '%';

        $params[] = $searchValue;
        $params[] = $searchValue;
        $params[] = $searchValue;
        $params[] = $searchValue;
    }

    if ($departmentId !== null && $departmentId > 0) {
        $where[] = "e.department_id = ?";
        $params[] = $departmentId;
    }

    if ($status !== null && in_array(
        $status,
        ['active', 'inactive', 'on_leave', 'terminated'],
        true
    )) {
        $where[] = "e.employment_status = ?";
        $params[] = $status;
    }

    $whereSql = '';

    if (!empty($where)) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    $sql = "
        SELECT COUNT(*)
        FROM employees e
        {$whereSql}
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}


public function getDepartments(): array {
    $stmt = $this->db->query("
        SELECT id, name
        FROM departments
        WHERE status = 'active'
        ORDER BY name ASC
    ");

    return $stmt->fetchAll();
}


public function getEmployeeWeeklySchedule(int $employeeId): array {
    $stmt = $this->db->prepare("
        SELECT
            es.id,
            es.employee_id,
            es.day_of_week,
            es.time_in,
            es.time_out,
            es.break_minutes,
            es.is_rest_day,
            es.created_at,

            e.employee_code,
            e.first_name,
            e.middle_name,
            e.last_name,

            d.name AS department_name,

            p.name AS position_name

        FROM employee_schedules es

        INNER JOIN employees e
            ON e.id = es.employee_id

        LEFT JOIN departments d
            ON d.id = e.department_id

        LEFT JOIN positions p
            ON p.id = e.position_id

        WHERE es.employee_id = ?

        ORDER BY es.day_of_week ASC
    ");

    $stmt->execute([$employeeId]);

    return $stmt->fetchAll();
}
}