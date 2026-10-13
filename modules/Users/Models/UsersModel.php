<?php

class UsersModel extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Get all users
    |--------------------------------------------------------------------------
    */

    public function getAll(): array
    {
        $sql = "
            SELECT
                u.id,
                u.username,
                u.email,
                u.full_name,
                u.status,
                u.last_login,
                u.created_at,

                GROUP_CONCAT(
                    DISTINCT r.name
                    ORDER BY r.name
                    SEPARATOR ', '
                ) AS roles

            FROM users u

            LEFT JOIN user_roles ur
                ON ur.user_id = u.id

            LEFT JOIN roles r
                ON r.id = ur.role_id

            GROUP BY
                u.id,
                u.username,
                u.email,
                u.full_name,
                u.status,
                u.last_login,
                u.created_at

            ORDER BY u.id DESC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | Find user
    |--------------------------------------------------------------------------
    */

    public function find(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                username,
                email,
                full_name,
                status,
                last_login,
                created_at,
                updated_at
            FROM users
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Find by username
    |--------------------------------------------------------------------------
    */

    public function findByUsername(string $username): ?array
    {
        $sql = "
            SELECT *
            FROM users
            WHERE username = :username
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'username' => $username
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Find by email
    |--------------------------------------------------------------------------
    */

    public function findByEmail(string $email): ?array
    {
        $sql = "
            SELECT *
            FROM users
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'email' => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Create user
    |--------------------------------------------------------------------------
    */

    public function create(array $data): int
{
    $stmt = $this->db->prepare("
        INSERT INTO users (
            username,
            email,
            password,
            full_name,
            status
        )
        VALUES (
            :username,
            :email,
            :password,
            :full_name,
            :status
        )
    ");

    $stmt->execute([
        'username'  => $data['username'],
        'email'     => $data['email'],
        'password'  => $data['password'],
        'full_name' => $data['full_name'],
        'status'    => $data['status']
    ]);

    return (int)$this->db->lastInsertId();
}

    /*
    |--------------------------------------------------------------------------
    | Update user
    |--------------------------------------------------------------------------
    */

    public function update(
        int $id,
        array $data
    ): bool {

        $sql = "
            UPDATE users
            SET
                username = :username,
                email = :email,
                full_name = :full_name,
                status = :status
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id'        => $id,
            'username'  => $data['username'],
            'email'     => $data['email'],
            'full_name' => $data['full_name'],
            'status'    => $data['status']
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update password
    |--------------------------------------------------------------------------
    */

    public function updatePassword(
        int $id,
        string $password
    ): bool {

        $sql = "
            UPDATE users
            SET password = :password
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id'       => $id,
            'password' => $password
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(int $id): bool
    {
        $sql = "
            UPDATE users
            SET status =
                CASE
                    WHEN status = 'active'
                    THEN 'inactive'

                    ELSE 'active'
                END
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Delete user
    |--------------------------------------------------------------------------
    */

    public function delete(int $id): bool
    {
        $sql = "
            DELETE FROM users
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Get user's roles
    |--------------------------------------------------------------------------
    */

    public function getRoles(int $userId): array
    {
        $sql = "
            SELECT
                r.id,
                r.name
            FROM roles r

            INNER JOIN user_roles ur
                ON ur.role_id = r.id

            WHERE ur.user_id = :user_id

            ORDER BY r.name
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | Get all roles
    |--------------------------------------------------------------------------
    */

    public function getAllRoles(): array
    {
        $sql = "
            SELECT
                id,
                name,
                description
            FROM roles
            ORDER BY name
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | Set user roles
    |--------------------------------------------------------------------------
    */

   public function setRoles(int $userId, array $roleIds): void
{
    // Remove existing roles first
    $stmt = $this->db->prepare("
        DELETE FROM user_roles
        WHERE user_id = :user_id
    ");

    $stmt->execute([
        'user_id' => $userId
    ]);

    // Add selected roles
    if (empty($roleIds)) {
        return;
    }

    $stmt = $this->db->prepare("
        INSERT INTO user_roles (user_id, role_id)
        VALUES (:user_id, :role_id)
    ");

    foreach ($roleIds as $roleId) {

        $roleId = (int)$roleId;

        if ($roleId <= 0) {
            continue;
        }

        $stmt->execute([
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }
}
}