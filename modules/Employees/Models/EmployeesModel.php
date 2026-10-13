<?php

class EmployeesModel extends Model
{
    public function getAll(): array
    {
        $stmt = $this->db->query("
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

            INNER JOIN departments d
                ON d.id = e.department_id

            INNER JOIN positions p
                ON p.id = e.position_id

            ORDER BY e.last_name ASC,
                     e.first_name ASC
        ");

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                e.*,
                d.name AS department_name,
                p.name AS position_name

            FROM employees e

            INNER JOIN departments d
                ON d.id = e.department_id

            INNER JOIN positions p
                ON p.id = e.position_id

            WHERE e.id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $employee = $stmt->fetch();

        return $employee ?: null;
    }

    public function findByEmployeeCode(
        string $employeeCode,
        ?int $excludeId = null
    ): ?array {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare("
                SELECT *
                FROM employees
                WHERE employee_code = ?
                AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $employeeCode,
                $excludeId
            ]);
        } else {
            $stmt = $this->db->prepare("
                SELECT *
                FROM employees
                WHERE employee_code = ?
                LIMIT 1
            ");

            $stmt->execute([
                $employeeCode
            ]);
        }

        $employee = $stmt->fetch();

        return $employee ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO employees (
                employee_code,
                first_name,
                middle_name,
                last_name,
                email,
                phone,
                address,
                department_id,
                position_id,
                hire_date,
                employment_status,
                salary_type,
                basic_salary
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        $stmt->execute([
            $data['employee_code'],
            $data['first_name'],
            $data['middle_name'] ?: null,
            $data['last_name'],
            $data['email'] ?: null,
            $data['phone'] ?: null,
            $data['address'] ?: null,
            $data['department_id'],
            $data['position_id'],
            $data['hire_date'],
            $data['employment_status'],
            $data['salary_type'],
            $data['basic_salary']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(
        int $id,
        array $data
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE employees
            SET
                employee_code = ?,
                first_name = ?,
                middle_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                address = ?,
                department_id = ?,
                position_id = ?,
                hire_date = ?,
                employment_status = ?,
                salary_type = ?,
                basic_salary = ?

            WHERE id = ?
        ");

        return $stmt->execute([
            $data['employee_code'],
            $data['first_name'],
            $data['middle_name'] ?: null,
            $data['last_name'],
            $data['email'] ?: null,
            $data['phone'] ?: null,
            $data['address'] ?: null,
            $data['department_id'],
            $data['position_id'],
            $data['hire_date'],
            $data['employment_status'],
            $data['salary_type'],
            $data['basic_salary'],
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM employees
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    public function countEmployees(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM employees
        ");

        return (int) $stmt->fetchColumn();
    }

    public function countActive(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM employees
            WHERE employment_status = 'active'
        ");

        return (int) $stmt->fetchColumn();
    }

    public function countByDepartment(
        int $departmentId
    ): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM employees
            WHERE department_id = ?
        ");

        $stmt->execute([$departmentId]);

        return (int) $stmt->fetchColumn();
    }

    public function getDepartments(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                status
            FROM departments
            WHERE status = 'active'
            ORDER BY name ASC
        ");

        return $stmt->fetchAll();
    }

    public function getPositions(
        ?int $departmentId = null
    ): array {
        if ($departmentId !== null) {
            $stmt = $this->db->prepare("
                SELECT
                    id,
                    department_id,
                    name,
                    salary_type,
                    basic_salary,
                    status
                FROM positions
                WHERE department_id = ?
                AND status = 'active'
                ORDER BY name ASC
            ");

            $stmt->execute([$departmentId]);

            return $stmt->fetchAll();
        }

        $stmt = $this->db->query("
            SELECT
                id,
                department_id,
                name,
                salary_type,
                basic_salary,
                status
            FROM positions
            WHERE status = 'active'
            ORDER BY name ASC
        ");

        return $stmt->fetchAll();
    }

    public function departmentExists(
        int $departmentId
    ): bool {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM departments
            WHERE id = ?
            AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([$departmentId]);

        return (bool) $stmt->fetchColumn();
    }

    public function positionExists(
        int $positionId,
        int $departmentId
    ): bool {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM positions
            WHERE id = ?
            AND department_id = ?
            AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([
            $positionId,
            $departmentId
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function getPosition(
        int $positionId
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT
                id,
                department_id,
                name,
                salary_type,
                basic_salary,
                status
            FROM positions
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$positionId]);

        $position = $stmt->fetch();

        return $position ?: null;
    }
}