<?php

class PositionsModel extends Model
{
    /**
     * Get all positions.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT
                p.id,
                p.department_id,
                p.name,
                p.description,
                p.salary_type,
                p.basic_salary,
                p.status,
                p.created_at,
                p.updated_at,
                d.name AS department_name,
                COUNT(e.id) AS employee_count
            FROM positions p
            INNER JOIN departments d
                ON d.id = p.department_id
            LEFT JOIN employees e
                ON e.position_id = p.id
            GROUP BY
                p.id,
                p.department_id,
                p.name,
                p.description,
                p.salary_type,
                p.basic_salary,
                p.status,
                p.created_at,
                p.updated_at,
                d.name
            ORDER BY
                d.name ASC,
                p.name ASC
        ");

        return $stmt->fetchAll();
    }


    /**
     * Get active departments.
     */
    public function getDepartments(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name
            FROM departments
            WHERE status = 'active'
            ORDER BY name ASC
        ");

        return $stmt->fetchAll();
    }


    /**
     * Find position.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.id,
                p.department_id,
                p.name,
                p.description,
                p.salary_type,
                p.basic_salary,
                p.status,
                p.created_at,
                p.updated_at,
                d.name AS department_name
            FROM positions p
            INNER JOIN departments d
                ON d.id = p.department_id
            WHERE p.id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $position = $stmt->fetch();

        return $position ?: null;
    }


    /**
     * Check duplicate position.
     */
    public function findByName(
        int $departmentId,
        string $name,
        ?int $excludeId = null
    ): ?array {

        if ($excludeId !== null) {

            $stmt = $this->db->prepare("
                SELECT
                    id,
                    department_id,
                    name,
                    description,
                    salary_type,
                    basic_salary,
                    status
                FROM positions
                WHERE department_id = ?
                AND name = ?
                AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $departmentId,
                $name,
                $excludeId
            ]);

        } else {

            $stmt = $this->db->prepare("
                SELECT
                    id,
                    department_id,
                    name,
                    description,
                    salary_type,
                    basic_salary,
                    status
                FROM positions
                WHERE department_id = ?
                AND name = ?
                LIMIT 1
            ");

            $stmt->execute([
                $departmentId,
                $name
            ]);
        }

        $position = $stmt->fetch();

        return $position ?: null;
    }


    /**
     * Check department exists.
     */
    public function departmentExists(
        int $departmentId
    ): bool {

        $stmt = $this->db->prepare("
            SELECT 1
            FROM departments
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $departmentId
        ]);

        return (bool) $stmt->fetchColumn();
    }


    /**
     * Create position.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO positions (
                department_id,
                name,
                description,
                salary_type,
                basic_salary,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['department_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['salary_type'],
            $data['basic_salary'],
            $data['status']
        ]);

        return (int) $this->db->lastInsertId();
    }


    /**
     * Update position.
     */
    public function update(
        int $id,
        array $data
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE positions
            SET
                department_id = ?,
                name = ?,
                description = ?,
                salary_type = ?,
                basic_salary = ?,
                status = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['department_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['salary_type'],
            $data['basic_salary'],
            $data['status'],
            $id
        ]);
    }


    /**
     * Count employees.
     */
    public function countEmployees(int $id): int
    {
        try {

            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM employees
                WHERE position_id = ?
            ");

            $stmt->execute([$id]);

            return (int) $stmt->fetchColumn();

        } catch (Throwable $e) {

            /*
             * Employees table will be created
             * in the next module.
             */
            return 0;
        }
    }


    /**
     * Toggle position status.
     */
    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE positions
            SET status =
                CASE
                    WHEN status = 'active'
                    THEN 'inactive'
                    ELSE 'active'
                END
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }


    /**
     * Delete position.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM positions
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
}