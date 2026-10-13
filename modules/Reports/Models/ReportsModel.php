<?php

class ReportsModel extends Model
{
    private function rangeWhere(): string
    {
        return "status = 'paid' AND COALESCE(paid_at, created_at) >= ? AND COALESCE(paid_at, created_at) < DATE_ADD(?, INTERVAL 1 DAY)";
    }

    public function getSummary(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS order_count,
                COALESCE(SUM(total), 0) AS income,
                COALESCE(SUM(subtotal), 0) AS subtotal,
                COALESCE(SUM(discount), 0) AS discounts,
                COALESCE(SUM(tax), 0) AS tax,
                COALESCE(AVG(total), 0) AS average_order
            FROM orders
            WHERE {$this->rangeWhere()}
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $summary = $stmt->fetch() ?: [];

        foreach (['order_count'] as $field) {
            $summary[$field] = (int) ($summary[$field] ?? 0);
        }
        foreach (['income', 'subtotal', 'discounts', 'tax', 'average_order'] as $field) {
            $summary[$field] = (float) ($summary[$field] ?? 0);
        }

        return $summary;
    }

    public function getPaymentBreakdown(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COALESCE(NULLIF(payment_method, ''), 'unknown') AS payment_method,
                COUNT(*) AS order_count,
                COALESCE(SUM(total), 0) AS income
            FROM orders
            WHERE {$this->rangeWhere()}
            GROUP BY COALESCE(NULLIF(payment_method, ''), 'unknown')
            ORDER BY income DESC
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }

    public function getTopProducts(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare("
            SELECT
                oi.product_id,
                oi.product_name,
                SUM(oi.quantity) AS quantity_sold,
                SUM(oi.subtotal) AS sales_total
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE o.status = 'paid'
              AND COALESCE(o.paid_at, o.created_at) >= ?
              AND COALESCE(o.paid_at, o.created_at) < DATE_ADD(?, INTERVAL 1 DAY)
            GROUP BY oi.product_id, oi.product_name
            ORDER BY quantity_sold DESC, sales_total DESC
            LIMIT 10
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }

    public function countOrders(string $dateFrom, string $dateTo): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM orders
            WHERE {$this->rangeWhere()}
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        return (int) $stmt->fetchColumn();
    }

    public function getOrders(
        string $dateFrom,
        string $dateTo,
        int $page = 1,
        int $perPage = 25
    ): array
    {
        $perPage = max(1, min($perPage, 100));
        $offset = (max(1, $page) - 1) * $perPage;

        $stmt = $this->db->prepare("
            SELECT
                o.id,
                o.order_number,
                o.order_type,
                o.total,
                o.payment_method,
                o.amount_tendered,
                o.change_amount,
                COALESCE(o.paid_at, o.created_at) AS completed_at,
                rt.table_number,
                u.full_name AS cashier_name
            FROM orders o
            LEFT JOIN restaurant_tables rt ON rt.id = o.table_id
            LEFT JOIN users u ON u.id = o.cashier_id
            WHERE o.status = 'paid'
              AND COALESCE(o.paid_at, o.created_at) >= ?
              AND COALESCE(o.paid_at, o.created_at) < DATE_ADD(?, INTERVAL 1 DAY)
            ORDER BY COALESCE(o.paid_at, o.created_at) DESC, o.id DESC
                        LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
}
