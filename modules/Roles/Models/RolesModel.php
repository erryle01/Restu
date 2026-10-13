<?php

class RolesModel extends Model
{
    /**
     * Get all roles with user count and permission count.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT
                r.id,
                r.name,
                r.description,
                r.created_at,
                r.updated_at,

                COUNT(DISTINCT ur.user_id) AS user_count,

                COUNT(DISTINCT rp.permission_id) AS permission_count

            FROM roles r

            LEFT JOIN user_roles ur
                ON ur.role_id = r.id

            LEFT JOIN role_permissions rp
                ON rp.role_id = r.id

            GROUP BY
                r.id,
                r.name,
                r.description,
                r.created_at,
                r.updated_at

            ORDER BY r.name ASC
        ");

        return $stmt->fetchAll();
    }


    /**
     * Find role by ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                name,
                description,
                created_at,
                updated_at
            FROM roles
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $role = $stmt->fetch();

        return $role ?: null;
    }


    /**
     * Find role by name.
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
                    created_at,
                    updated_at
                FROM roles
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
                    created_at,
                    updated_at
                FROM roles
                WHERE name = ?
                LIMIT 1
            ");

            $stmt->execute([
                $name
            ]);
        }

        $role = $stmt->fetch();

        return $role ?: null;
    }


    /**
     * Create a new role.
     *
     * @param array $data
     * @return int Role ID
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO roles (
                name,
                description
            )
            VALUES (
                ?,
                ?
            )
        ");

        $stmt->execute([
            $data['name'],
            $data['description'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }


    /**
     * Update role.
     */
    public function update(
        int $id,
        array $data
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE roles
            SET
                name = ?,
                description = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $id
        ]);
    }


    /**
     * Delete role.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM roles
            WHERE id = ?
        ");

        return $stmt->execute([
            $id
        ]);
    }


    /**
     * Get all permissions.
     *
     * IMPORTANT:
     * Your permissions table uses `permission_key`,
     * NOT `name`.
     */
    public function getAllPermissions(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                permission_key,
                description,
                created_at
            FROM permissions
            ORDER BY permission_key ASC
        ");

        return $stmt->fetchAll();
    }


    /**
     * Get permission IDs assigned to a role.
     */
    public function getRolePermissions(int $roleId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                permission_id
            FROM role_permissions
            WHERE role_id = ?
        ");

        $stmt->execute([
            $roleId
        ]);

        return array_map(
            'intval',
            $stmt->fetchAll(PDO::FETCH_COLUMN)
        );
    }


    /**
 * Set permissions for a role.
 *
 * The transaction is controlled by the controller.
 * This method must NOT call beginTransaction(), commit(),
 * or rollBack().
 */
public function setPermissions(
    int $roleId,
    array $permissionIds
): bool {

    /*
     * Remove all existing permissions.
     */
    $stmt = $this->db->prepare("
        DELETE FROM role_permissions
        WHERE role_id = ?
    ");

    $stmt->execute([
        $roleId
    ]);


    /*
     * Convert permission IDs to integers
     * and remove duplicates.
     */
    $permissionIds = array_unique(
        array_map(
            'intval',
            $permissionIds
        )
    );


    /*
     * If no permissions were selected,
     * the role simply has no permissions.
     */
    if (empty($permissionIds)) {
        return true;
    }


    /*
     * Insert selected permissions.
     */
    $stmt = $this->db->prepare("
        INSERT INTO role_permissions (
            role_id,
            permission_id
        )
        VALUES (
            ?,
            ?
        )
    ");


    foreach ($permissionIds as $permissionId) {

        /*
         * Ignore invalid IDs.
         */
        if ($permissionId <= 0) {
            continue;
        }


        /*
         * Make sure the permission actually exists.
         */
        if (!$this->permissionExists($permissionId)) {
            continue;
        }


        $stmt->execute([
            $roleId,
            $permissionId
        ]);
    }


    return true;
}


    /**
     * Count users assigned to a role.
     */
    public function countUsers(int $roleId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM user_roles
            WHERE role_id = ?
        ");

        $stmt->execute([
            $roleId
        ]);

        return (int) $stmt->fetchColumn();
    }


    /**
     * Check whether a role exists.
     */
    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM roles
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $id
        ]);

        return (bool) $stmt->fetchColumn();
    }


    /**
     * Check whether a permission exists.
     */
    public function permissionExists(int $permissionId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM permissions
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $permissionId
        ]);

        return (bool) $stmt->fetchColumn();
    }


    /**
     * Get a permission by its permission key.
     *
     * Example:
     * dashboard.view
     * pos.create
     * roles.edit
     */
    public function findPermissionByKey(
        string $permissionKey
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT
                id,
                permission_key,
                description,
                created_at
            FROM permissions
            WHERE permission_key = ?
            LIMIT 1
        ");

        $stmt->execute([
            $permissionKey
        ]);

        $permission = $stmt->fetch();

        return $permission ?: null;
    }


    /**
     * Get all permissions assigned to a role,
     * including the permission key and description.
     */
    public function getRolePermissionsDetailed(
        int $roleId
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                p.id,
                p.permission_key,
                p.description,
                p.created_at

            FROM role_permissions rp

            INNER JOIN permissions p
                ON p.id = rp.permission_id

            WHERE rp.role_id = ?

            ORDER BY p.permission_key ASC
        ");

        $stmt->execute([
            $roleId
        ]);

        return $stmt->fetchAll();
    }


    /**
     * Get users assigned to a role.
     */
    public function getUsers(int $roleId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.id,
                u.username,
                u.email,
                u.full_name,
                u.status

            FROM user_roles ur

            INNER JOIN users u
                ON u.id = ur.user_id

            WHERE ur.role_id = ?

            ORDER BY u.full_name ASC
        ");

        $stmt->execute([
            $roleId
        ]);

        return $stmt->fetchAll();
    }


    /**
     * Assign a single permission to a role.
     */
    public function addPermission(
        int $roleId,
        int $permissionId
    ): bool {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO role_permissions (
                role_id,
                permission_id
            )
            VALUES (
                ?,
                ?
            )
        ");

        return $stmt->execute([
            $roleId,
            $permissionId
        ]);
    }


    /**
     * Remove a single permission from a role.
     */
    public function removePermission(
        int $roleId,
        int $permissionId
    ): bool {
        $stmt = $this->db->prepare("
            DELETE FROM role_permissions
            WHERE role_id = ?
            AND permission_id = ?
        ");

        return $stmt->execute([
            $roleId,
            $permissionId
        ]);
    }


    /**
     * Remove all permissions from a role.
     */
    public function removeAllPermissions(
        int $roleId
    ): bool {
        $stmt = $this->db->prepare("
            DELETE FROM role_permissions
            WHERE role_id = ?
        ");

        return $stmt->execute([
            $roleId
        ]);
    }
}