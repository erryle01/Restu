<?php

class AuthModel extends Model
{
    public function findUser(
        string $username
    ): ?array {

        $sql = "
            SELECT
                id,
                username,
                email,
                password,
                full_name,
                status,
                last_login
            FROM users
            WHERE username = :username
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'username' => $username
        ]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function getRoles(
        int $userId
    ): array {

        $sql = "
            SELECT r.name
            FROM roles r
            INNER JOIN user_roles ur
                ON ur.role_id = r.id
            WHERE ur.user_id = :user_id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_COLUMN
        );
    }

    public function getPermissions(
        int $userId
    ): array {

        $sql = "
            SELECT DISTINCT p.permission_key
            FROM permissions p
            INNER JOIN role_permissions rp
                ON rp.permission_id = p.id
            INNER JOIN user_roles ur
                ON ur.role_id = rp.role_id
            WHERE ur.user_id = :user_id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_COLUMN
        );
    }

    public function updateLastLogin(
        int $userId
    ): void {

        $sql = "
            UPDATE users
            SET last_login = NOW()
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $userId
        ]);
    }

    public function logAttempt(
        ?string $username,
        bool $success
    ): void {

        $sql = "
            INSERT INTO login_attempts
            (
                username,
                ip_address,
                user_agent,
                success
            )
            VALUES
            (
                :username,
                :ip,
                :user_agent,
                :success
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'username'   => $username,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'success'    => $success ? 1 : 0
        ]);
    }
}