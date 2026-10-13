<?php

class DashboardModel extends Model
{
    /**
     * Count all users.
     */
    public function getTotalUsers(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM users
        ");

        return (int) $stmt->fetchColumn();
    }


    /**
     * Count active users.
     */
    public function getActiveUsers(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM users
            WHERE status = 'active'
        ");

        return (int) $stmt->fetchColumn();
    }


    /**
     * Count inactive users.
     */
    public function getInactiveUsers(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM users
            WHERE status = 'inactive'
        ");

        return (int) $stmt->fetchColumn();
    }


    /**
     * Count locked users.
     */
    public function getLockedUsers(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM users
            WHERE status = 'locked'
        ");

        return (int) $stmt->fetchColumn();
    }


    /**
     * Count roles.
     */
    public function getTotalRoles(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM roles
        ");

        return (int) $stmt->fetchColumn();
    }


    /**
     * Count permissions.
     */
    public function getTotalPermissions(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM permissions
        ");

        return (int) $stmt->fetchColumn();
    }


    /**
     * Count employees.
     *
     * Only execute this if the employees table
     * already exists.
     */
    public function getTotalEmployees(): int
    {
        try {

            $stmt = $this->db->query("
                SELECT COUNT(*)
                FROM employees
            ");

            return (int) $stmt->fetchColumn();

        } catch (Throwable $e) {

            return 0;
        }
    }

    public function getSalesSummary(string $dateFrom, string $dateTo): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) AS order_count,
                    COALESCE(SUM(total), 0) AS income,
                    COALESCE(SUM(discount), 0) AS discounts
                FROM orders
                WHERE status = 'paid'
                  AND COALESCE(paid_at, created_at) >= ?
                  AND COALESCE(paid_at, created_at) < DATE_ADD(?, INTERVAL 1 DAY)
            ");
            $stmt->execute([$dateFrom, $dateTo]);
            $summary = $stmt->fetch();

            return [
                'order_count' => (int) ($summary['order_count'] ?? 0),
                'income' => (float) ($summary['income'] ?? 0),
                'discounts' => (float) ($summary['discounts'] ?? 0)
            ];
        } catch (Throwable $exception) {
            return [
                'order_count' => 0,
                'income' => 0.0,
                'discounts' => 0.0
            ];
        }
    }

    public function getDailySales(int $days = 7): array
    {
        $days = max(1, min($days, 31));
        $today = new DateTimeImmutable('today');
        $start = $today->modify('-' . ($days - 1) . ' days');
        $daily = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $date = $start->modify('+' . $offset . ' days')->format('Y-m-d');
            $daily[$date] = [
                'date' => $date,
                'income' => 0.0,
                'order_count' => 0
            ];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT
                    DATE(COALESCE(paid_at, created_at)) AS sales_date,
                    COUNT(*) AS order_count,
                    COALESCE(SUM(total), 0) AS income
                FROM orders
                WHERE status = 'paid'
                  AND COALESCE(paid_at, created_at) >= ?
                  AND COALESCE(paid_at, created_at) < DATE_ADD(?, INTERVAL 1 DAY)
                GROUP BY DATE(COALESCE(paid_at, created_at))
            ");
            $stmt->execute([$start->format('Y-m-d'), $today->format('Y-m-d')]);

            foreach ($stmt->fetchAll() as $row) {
                $date = $row['sales_date'];
                if (isset($daily[$date])) {
                    $daily[$date]['income'] = (float) $row['income'];
                    $daily[$date]['order_count'] = (int) $row['order_count'];
                }
            }
        } catch (Throwable $exception) {
            return array_values($daily);
        }

        return array_values($daily);
    }


    /**
     * Get recent users.
     */
    public function getRecentUsers(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));

        $stmt = $this->db->query("
            SELECT
                id,
                username,
                email,
                full_name,
                status,
                created_at
            FROM users
            ORDER BY created_at DESC
            LIMIT {$limit}
        ");

        return $stmt->fetchAll();
    }


    /**
     * Get recent activity logs.
     *
     * Returns an empty array if activity_logs
     * does not exist yet.
     */
    public function getRecentActivities(
        int $limit = 10
    ): array {

        $limit = max(1, min($limit, 20));

        try {

            $stmt = $this->db->query("
                SELECT
                    id,
                    user_id,
                    module,
                    action,
                    reference_id,
                    description,
                    ip_address,
                    created_at
                FROM activity_logs
                ORDER BY created_at DESC
                LIMIT {$limit}
            ");

            return $stmt->fetchAll();

        } catch (Throwable $e) {

            return [];
        }
    }
}