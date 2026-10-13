<?php

class DepartmentsModel extends Model
{
    /**
     * Get all departments.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT
                d.id,
                d.name,
                d.description,
                d.status,
                d.created_at,
                d.updated_at,
                COUNT(e.id) AS employee_count
            FROM departments d
            LEFT JOIN employees e
                ON e.department_id = d.id
            GROUP BY
                d.id,
                d.name,
                d.description,
                d.status,
                d.created_at,
                d.updated_at
            ORDER BY d.name ASC
        ");

        return $stmt->fetchAll();
    }


    /**
     * Find department by ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                name,
                description,
                status,
                created_at,
                updated_at
            FROM departments
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $department = $stmt->fetch();

        return $department ?: null;
    }


    /**
     * Find department by name.
     */
    public function findByName(
        string $name,
        ?int $excludeId = null
    ): ?array {

        if ($excludeId !== null) {

            $stmt = $this->db->prepare("
                SELECT
                    id,
                    name,
                    description,
                    status
                FROM departments
                WHERE name = ?
                AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $name,
                $excludeId
            ]);

        } else {

            $stmt = $this->db->prepare("
                SELECT
                    id,
                    name,
                    description,
                    status
                FROM departments
                WHERE name = ?
                LIMIT 1
            ");

            $stmt->execute([
                $name
            ]);
        }

        $department = $stmt->fetch();

        return $department ?: null;
    }


    /**
     * Create department.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO departments (
                name,
                description,
                status
            )
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['status'] ?? 'active'
        ]);

        return (int) $this->db->lastInsertId();
    }


    /**
     * Update department.
     */
    public function update(
        int $id,
        array $data
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE departments
            SET
                name = ?,
                description = ?,
                status = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
    }


    /**
     * Toggle department status.
     */
    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE departments
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
     * Count employees assigned to department.
     */
    public function countEmployees(int $id): int
    {
        try {

            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM employees
                WHERE department_id = ?
            ");

            $stmt->execute([$id]);

            return (int) $stmt->fetchColumn();

        } catch (Throwable $e) {

            /*
             * Employees table may not exist yet.
             */
            return 0;
        }
    }


    /**
     * Delete department.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM departments
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
}