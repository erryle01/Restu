<?php

class PayrollModel extends Model
{
    /*
    |--------------------------------------------------------------------------
    | PAYROLL PERIODS
    |--------------------------------------------------------------------------
    */

    public function getPeriods(): array
    {
        $stmt = $this->db->query("
            SELECT
                pp.*,
                creator.full_name AS created_by_name,
                approver.full_name AS approved_by_name,
                COUNT(DISTINCT p.id) AS employee_count,
                COALESCE(SUM(p.gross_pay), 0) AS total_gross,
                COALESCE(SUM(p.total_deductions), 0) AS total_deductions,
                COALESCE(SUM(p.net_pay), 0) AS total_net
            FROM payroll_periods pp
            LEFT JOIN users creator
                ON creator.id = pp.created_by
            LEFT JOIN users approver
                ON approver.id = pp.approved_by
            LEFT JOIN payroll p
                ON p.payroll_period_id = pp.id
            GROUP BY
                pp.id,
                creator.full_name,
                approver.full_name
            ORDER BY pp.start_date DESC, pp.id DESC
        ");

        return $stmt->fetchAll();
    }

    public function findPeriod(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                pp.*,
                creator.full_name AS created_by_name,
                approver.full_name AS approved_by_name
            FROM payroll_periods pp
            LEFT JOIN users creator
                ON creator.id = pp.created_by
            LEFT JOIN users approver
                ON approver.id = pp.approved_by
            WHERE pp.id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findOverlappingPeriod(
        string $startDate,
        string $endDate,
        ?int $excludeId = null
    ): ?array {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare("
                SELECT *
                FROM payroll_periods
                WHERE start_date <= ?
                  AND end_date >= ?
                  AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $endDate,
                $startDate,
                $excludeId
            ]);
        } else {
            $stmt = $this->db->prepare("
                SELECT *
                FROM payroll_periods
                WHERE start_date <= ?
                  AND end_date >= ?
                LIMIT 1
            ");

            $stmt->execute([
                $endDate,
                $startDate
            ]);
        }

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function createPeriod(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO payroll_periods (
                period_name,
                start_date,
                end_date,
                pay_date,
                status,
                created_by
            )
            VALUES (?, ?, ?, ?, 'draft', ?)
        ");

        $stmt->execute([
            $data['period_name'],
            $data['start_date'],
            $data['end_date'],
            $data['pay_date'] ?: null,
            $data['created_by'] ?: null
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updatePeriod(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE payroll_periods
            SET
                period_name = ?,
                start_date = ?,
                end_date = ?,
                pay_date = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['period_name'],
            $data['start_date'],
            $data['end_date'],
            $data['pay_date'] ?: null,
            $id
        ]);
    }

    public function updatePeriodStatus(
        int $id,
        string $status
    ): bool {
        $allowed = [
            'draft',
            'processing',
            'review',
            'approved',
            'paid',
            'locked'
        ];

        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE payroll_periods
            SET status = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $status,
            $id
        ]);
    }

    public function approvePeriod(
        int $id,
        int $approvedBy
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE payroll_periods
            SET
                status = 'approved',
                approved_by = ?,
                approved_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([
            $approvedBy,
            $id
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEES
    |--------------------------------------------------------------------------
    */

    public function getActiveEmployees(): array
    {
        $stmt = $this->db->query("
            SELECT
                e.*,
                d.name AS department_name,
                p.name AS position_name
            FROM employees e
            LEFT JOIN departments d
                ON d.id = e.department_id
            LEFT JOIN positions p
                ON p.id = e.position_id
            WHERE e.employment_status = 'active'
            ORDER BY e.last_name ASC, e.first_name ASC
        ");

        return $stmt->fetchAll();
    }

    public function findEmployee(int $employeeId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                e.*,
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

        $stmt->execute([$employeeId]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE
    |--------------------------------------------------------------------------
    */

    public function getAttendance(
        int $employeeId,
        string $startDate,
        string $endDate
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                a.*,

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
                ) AS overtime_hours

            FROM attendance a

            WHERE a.employee_id = ?
              AND a.attendance_date BETWEEN ? AND ?

            ORDER BY a.attendance_date ASC
        ");

        $stmt->execute([
            $employeeId,
            $startDate,
            $endDate
        ]);

        return $stmt->fetchAll();
    }

    public function getScheduledWorkdays(
        int $employeeId,
        string $startDate,
        string $endDate
    ): int {
        $stmt = $this->db->prepare("
            SELECT day_of_week
            FROM employee_schedules
            WHERE employee_id = ?
              AND is_rest_day = 0
        ");
        $stmt->execute([$employeeId]);
        $scheduledDays = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        if (!$scheduledDays) {
            return 0;
        }

        $start = new DateTimeImmutable($startDate);
        $end = new DateTimeImmutable($endDate);
        $count = 0;

        for ($date = $start; $date <= $end; $date = $date->modify('+1 day')) {
            if (in_array((int) $date->format('N'), $scheduledDays, true)) {
                $count++;
            }
        }

        return $count;
    }

    /*
    |--------------------------------------------------------------------------
    | PAYROLL
    |--------------------------------------------------------------------------
    */

    public function getPayrollByPeriod(
        int $periodId
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                p.*,

                e.employee_code,
                e.first_name,
                e.middle_name,
                e.last_name,

                d.name AS department_name,
                pos.name AS position_name

            FROM payroll p

            INNER JOIN employees e
                ON e.id = p.employee_id

            LEFT JOIN departments d
                ON d.id = e.department_id

            LEFT JOIN positions pos
                ON pos.id = e.position_id

            WHERE p.payroll_period_id = ?

            ORDER BY
                e.last_name ASC,
                e.first_name ASC
        ");

        $stmt->execute([$periodId]);

        return $stmt->fetchAll();
    }

    public function findPayroll(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.*,

                e.employee_code,
                e.first_name,
                e.middle_name,
                e.last_name,
                e.email,
                e.phone,

                d.name AS department_name,
                pos.name AS position_name,

                pp.period_name,
                pp.start_date,
                pp.end_date,
                pp.pay_date,
                pp.status AS period_status

            FROM payroll p

            INNER JOIN employees e
                ON e.id = p.employee_id

            INNER JOIN payroll_periods pp
                ON pp.id = p.payroll_period_id

            LEFT JOIN departments d
                ON d.id = e.department_id

            LEFT JOIN positions pos
                ON pos.id = e.position_id

            WHERE p.id = ?

            LIMIT 1
        ");

        $stmt->execute([$id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findPayrollByEmployeePeriod(
        int $periodId,
        int $employeeId
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT *
            FROM payroll
            WHERE payroll_period_id = ?
              AND employee_id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $periodId,
            $employeeId
        ]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function createPayroll(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO payroll (
                payroll_period_id,
                employee_id,
                basic_pay,
                overtime_pay,
                allowance,
                incentive,
                gross_pay,
                total_deductions,
                net_pay,
                status,
                calculated_at
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?,
                'calculated',
                NOW()
            )
        ");

        $stmt->execute([
            $data['payroll_period_id'],
            $data['employee_id'],
            $data['basic_pay'],
            $data['overtime_pay'],
            $data['allowance'],
            $data['incentive'],
            $data['gross_pay'],
            $data['total_deductions'],
            $data['net_pay']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updatePayroll(
        int $id,
        array $data
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE payroll
            SET
                basic_pay = ?,
                overtime_pay = ?,
                allowance = ?,
                incentive = ?,
                gross_pay = ?,
                total_deductions = ?,
                net_pay = ?,
                status = 'calculated',
                calculated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['basic_pay'],
            $data['overtime_pay'],
            $data['allowance'],
            $data['incentive'],
            $data['gross_pay'],
            $data['total_deductions'],
            $data['net_pay'],
            $id
        ]);
    }

    public function updatePayrollStatus(
        int $id,
        string $status
    ): bool {
        $allowed = [
            'draft',
            'calculated',
            'approved',
            'paid',
            'locked'
        ];

        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE payroll
            SET status = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $status,
            $id
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PAYROLL ITEMS
    |--------------------------------------------------------------------------
    */

    public function deletePayrollItems(
        int $payrollId
    ): bool {
        $stmt = $this->db->prepare("
            DELETE FROM payroll_items
            WHERE payroll_id = ?
        ");

        return $stmt->execute([
            $payrollId
        ]);
    }

    public function createPayrollItem(
        array $data
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO payroll_items (
                payroll_id,
                item_type,
                item_code,
                item_name,
                amount,
                remarks
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['payroll_id'],
            $data['item_type'],
            $data['item_code'],
            $data['item_name'],
            $data['amount'],
            $data['remarks'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getPayrollItems(
        int $payrollId
    ): array {
        $stmt = $this->db->prepare("
            SELECT *
            FROM payroll_items
            WHERE payroll_id = ?
            ORDER BY
                item_type ASC,
                id ASC
        ");

        $stmt->execute([
            $payrollId
        ]);

        return $stmt->fetchAll();
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENTS
    |--------------------------------------------------------------------------
    */

    public function createPayment(
        array $data
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO payroll_payments (
                payroll_id,
                payment_method,
                amount,
                reference_number,
                paid_by
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['payroll_id'],
            $data['payment_method'],
            $data['amount'],
            $data['reference_number'] ?: null,
            $data['paid_by'] ?: null
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getPayments(
        int $payrollId
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                pp.*,
                u.full_name AS paid_by_name
            FROM payroll_payments pp
            LEFT JOIN users u
                ON u.id = pp.paid_by
            WHERE pp.payroll_id = ?
            ORDER BY pp.paid_at DESC
        ");

        $stmt->execute([
            $payrollId
        ]);

        return $stmt->fetchAll();
    }
}