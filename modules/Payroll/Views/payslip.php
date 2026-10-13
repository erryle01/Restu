<?php
/**
 * Restu Payroll System
 * Payslip View
 *
 * Expected variables:
 * $payroll
 * $items
 * $payments
 */

$payroll   = $payroll ?? [];
$items     = $items ?? [];
$payments  = $payments ?? [];

$employee = $payroll['employee'] ?? [];

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

$e = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
};

$money = static function ($value): string {
    return '₱' . number_format((float) ($value ?? 0), 2);
};

$date = static function ($value): string {
    if (empty($value)) {
        return '—';
    }

    $timestamp = strtotime($value);

    return $timestamp
        ? date('M d, Y', $timestamp)
        : $value;
};

$datetime = static function ($value): string {
    if (empty($value)) {
        return '—';
    }

    $timestamp = strtotime($value);

    return $timestamp
        ? date('M d, Y h:i A', $timestamp)
        : $value;
};

/*
|--------------------------------------------------------------------------
| Employee information
|--------------------------------------------------------------------------
*/

$employeeName = trim(
    ($payroll['first_name'] ?? $employee['first_name'] ?? '') . ' ' .
    ($payroll['middle_name'] ?? $employee['middle_name'] ?? '') . ' ' .
    ($payroll['last_name'] ?? $employee['last_name'] ?? '')
);

if ($employeeName === '') {
    $employeeName = $payroll['employee_name'] ?? 'Employee';
}

$employeeCode =
    $payroll['employee_code']
    ?? $employee['employee_code']
    ?? '—';

$department =
    $payroll['department_name']
    ?? $employee['department_name']
    ?? '—';

$position =
    $payroll['position_name']
    ?? $employee['position_name']
    ?? '—';

/*
|--------------------------------------------------------------------------
| Payroll information
|--------------------------------------------------------------------------
*/

$periodName = $payroll['period_name'] ?? 'Payroll Period';

$startDate =
    $payroll['start_date']
    ?? $payroll['period_start']
    ?? null;

$endDate =
    $payroll['end_date']
    ?? $payroll['period_end']
    ?? null;

$payDate =
    $payroll['pay_date']
    ?? null;

$status = $payroll['status'] ?? 'draft';

$basicPay       = (float) ($payroll['basic_pay'] ?? 0);
$overtimePay    = (float) ($payroll['overtime_pay'] ?? 0);
$allowance      = (float) ($payroll['allowance'] ?? 0);
$incentive      = (float) ($payroll['incentive'] ?? 0);
$grossPay       = (float) ($payroll['gross_pay'] ?? 0);
$totalDeductions = (float) ($payroll['total_deductions'] ?? 0);
$netPay         = (float) ($payroll['net_pay'] ?? 0);

/*
|--------------------------------------------------------------------------
| Separate earnings and deductions
|--------------------------------------------------------------------------
*/

$earnings = [];
$deductions = [];
$employerContributions = [];

foreach ($items as $item) {

    $type = $item['item_type'] ?? '';

    if ($type === 'earning') {
        $earnings[] = $item;
    } elseif ($type === 'deduction') {
        $deductions[] = $item;
    } elseif ($type === 'employer_contribution') {
        $employerContributions[] = $item;
    }
}

/*
|--------------------------------------------------------------------------
| Payment information
|--------------------------------------------------------------------------
*/

$totalPaid = 0;

foreach ($payments as $payment) {
    $totalPaid += (float) ($payment['amount'] ?? 0);
}

$remainingBalance = max($netPay - $totalPaid, 0);
?>

<style>
    .payslip-wrapper {
        max-width: 1000px;
        margin: 0 auto;
    }

    .payslip-paper {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 40px;
    }

    .company-header {
        border-bottom: 2px solid #212529;
        padding-bottom: 20px;
        margin-bottom: 25px;
    }

    .company-name {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .payslip-title {
        font-size: 22px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .info-box {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 16px;
        height: 100%;
    }

    .info-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .info-value {
        font-weight: 600;
        color: #212529;
    }

    .section-title {
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 8px;
        margin-bottom: 0;
    }

    .amount {
        text-align: right;
        white-space: nowrap;
    }

    .total-row td {
        font-weight: 700;
        border-top: 2px solid #212529;
    }

    .net-pay-box {
        background: #f8f9fa;
        border: 2px solid #212529;
        border-radius: 10px;
        padding: 20px;
    }

    .net-pay-label {
        font-size: 13px;
        text-transform: uppercase;
        font-weight: 700;
    }

    .net-pay-value {
        font-size: 30px;
        font-weight: 800;
    }

    .signature-line {
        border-top: 1px solid #212529;
        width: 220px;
        margin-top: 55px;
        padding-top: 7px;
        text-align: center;
        font-size: 13px;
    }

    .status-badge {
        text-transform: capitalize;
    }

    @media print {

        body {
            background: #fff !important;
        }

        .sidebar,
        .topbar,
        .no-print,
        nav,
        footer {
            display: none !important;
        }

        .main-content,
        .content,
        .container,
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            max-width: none !important;
        }

        .payslip-wrapper {
            max-width: none;
        }

        .payslip-paper {
            border: none;
            border-radius: 0;
            padding: 20px;
        }

        .card {
            box-shadow: none !important;
        }

        @page {
            size: A4;
            margin: 12mm;
        }
    }
</style>

<div class="container-fluid py-4">

    <div class="payslip-wrapper">

        <!-- ACTIONS -->
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">

            <a
                href="<?= BASE_URL ?>/payroll/view/<?= (int) ($payroll['id'] ?? 0) ?>"
                class="btn btn-outline-secondary"
            >
                <i data-lucide="arrow-left"></i>
                Back
            </a>

            <button
                type="button"
                class="btn btn-dark"
                onclick="window.print()"
            >
                <i data-lucide="printer"></i>
                Print Payslip
            </button>

        </div>

        <div class="payslip-paper">

            <!-- COMPANY HEADER -->
            <div class="company-header">

                <div class="row align-items-start">

                    <div class="col-md-7">

                        <div class="company-name">
                            <?= $e(
                                $companyName
                                ?? 'My Company'
                            ) ?>
                        </div>

                        <?php if (!empty($companyAddress ?? '')): ?>
                            <div class="text-muted small">
                                <?= $e($companyAddress) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($companyPhone ?? '')): ?>
                            <div class="text-muted small">
                                <?= $e($companyPhone) ?>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="col-md-5 text-md-end mt-3 mt-md-0">

                        <div class="payslip-title">
                            Employee Payslip
                        </div>

                        <div class="text-muted small mt-1">
                            <?= $e($periodName) ?>
                        </div>

                    </div>

                </div>

            </div>

            <!-- PAYROLL PERIOD -->
            <div class="row g-3 mb-4">

                <div class="col-md-3">

                    <div class="info-box">

                        <div class="info-label">
                            Payroll Period
                        </div>

                        <div class="info-value">
                            <?= $e($periodName) ?>
                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="info-box">

                        <div class="info-label">
                            Period Start
                        </div>

                        <div class="info-value">
                            <?= $date($startDate) ?>
                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="info-box">

                        <div class="info-label">
                            Period End
                        </div>

                        <div class="info-value">
                            <?= $date($endDate) ?>
                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="info-box">

                        <div class="info-label">
                            Pay Date
                        </div>

                        <div class="info-value">
                            <?= $date($payDate) ?>
                        </div>

                    </div>

                </div>

            </div>

            <!-- EMPLOYEE INFORMATION -->
            <div class="section-title mb-3">
                Employee Information
            </div>

            <div class="row g-3 mb-4">

                <div class="col-md-4">

                    <div class="info-box">

                        <div class="info-label">
                            Employee
                        </div>

                        <div class="info-value">
                            <?= $e($employeeName) ?>
                        </div>

                    </div>

                </div>

                <div class="col-md-2">

                    <div class="info-box">

                        <div class="info-label">
                            Employee Code
                        </div>

                        <div class="info-value">
                            <?= $e($employeeCode) ?>
                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="info-box">

                        <div class="info-label">
                            Department
                        </div>

                        <div class="info-value">
                            <?= $e($department) ?>
                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="info-box">

                        <div class="info-label">
                            Position
                        </div>

                        <div class="info-value">
                            <?= $e($position) ?>
                        </div>

                    </div>

                </div>

            </div>

            <!-- STATUS -->
            <div class="mb-4">

                <span class="badge text-bg-secondary status-badge">
                    <?= $e($status) ?>
                </span>

            </div>

            <!-- EARNINGS / DEDUCTIONS -->
            <div class="row g-4">

                <!-- EARNINGS -->
                <div class="col-md-6">

                    <div class="section-title mb-0">
                        Earnings
                    </div>

                    <div class="table-responsive">

                        <table class="table table-sm align-middle mb-0">

                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="amount">Amount</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr>
                                    <td>
                                        Basic Pay
                                    </td>

                                    <td class="amount">
                                        <?= $money($basicPay) ?>
                                    </td>
                                </tr>

                                <?php if ($overtimePay > 0): ?>

                                    <tr>
                                        <td>
                                            Overtime Pay
                                        </td>

                                        <td class="amount">
                                            <?= $money($overtimePay) ?>
                                        </td>
                                    </tr>

                                <?php endif; ?>

                                <?php if ($allowance > 0): ?>

                                    <tr>
                                        <td>
                                            Allowance
                                        </td>

                                        <td class="amount">
                                            <?= $money($allowance) ?>
                                        </td>
                                    </tr>

                                <?php endif; ?>

                                <?php if ($incentive > 0): ?>

                                    <tr>
                                        <td>
                                            Incentive
                                        </td>

                                        <td class="amount">
                                            <?= $money($incentive) ?>
                                        </td>
                                    </tr>

                                <?php endif; ?>

                                <?php foreach ($earnings as $item): ?>

                                    <tr>

                                        <td>

                                            <?= $e(
                                                $item['item_name']
                                                ?? $item['item_code']
                                                ?? 'Earning'
                                            ) ?>

                                            <?php if (!empty($item['remarks'])): ?>

                                                <div class="text-muted small">
                                                    <?= $e($item['remarks']) ?>
                                                </div>

                                            <?php endif; ?>

                                        </td>

                                        <td class="amount">
                                            <?= $money($item['amount'] ?? 0) ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                <tr class="total-row">

                                    <td>
                                        Gross Pay
                                    </td>

                                    <td class="amount">
                                        <?= $money($grossPay) ?>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

                <!-- DEDUCTIONS -->
                <div class="col-md-6">

                    <div class="section-title mb-0">
                        Deductions
                    </div>

                    <div class="table-responsive">

                        <table class="table table-sm align-middle mb-0">

                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="amount">Amount</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (empty($deductions)): ?>

                                    <tr>

                                        <td
                                            colspan="2"
                                            class="text-muted text-center py-4"
                                        >
                                            No deductions
                                        </td>

                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($deductions as $item): ?>

                                        <tr>

                                            <td>

                                                <?= $e(
                                                    $item['item_name']
                                                    ?? $item['item_code']
                                                    ?? 'Deduction'
                                                ) ?>

                                                <?php if (!empty($item['remarks'])): ?>

                                                    <div class="text-muted small">
                                                        <?= $e($item['remarks']) ?>
                                                    </div>

                                                <?php endif; ?>

                                            </td>

                                            <td class="amount">
                                                <?= $money($item['amount'] ?? 0) ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                                <tr class="total-row">

                                    <td>
                                        Total Deductions
                                    </td>

                                    <td class="amount">
                                        <?= $money($totalDeductions) ?>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <?php if (!empty($employerContributions)): ?>
                <div class="row mt-3">
                    <div class="col-md-6 ms-auto">
                        <div class="section-title mb-0">Employer contributions</div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Contribution</th><th class="amount">Employer cost</th></tr></thead>
                                <tbody>
                                    <?php foreach ($employerContributions as $item): ?>
                                        <tr>
                                            <td>
                                                <?= $e($item['item_name'] ?? $item['item_code'] ?? 'Employer contribution') ?>
                                                <?php if (!empty($item['remarks'])): ?><div class="text-muted small"><?= $e($item['remarks']) ?></div><?php endif; ?>
                                            </td>
                                            <td class="amount"><?= $money($item['amount'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- NET PAY -->
            <div class="row justify-content-end mt-4">

                <div class="col-md-5">

                    <div class="net-pay-box">

                        <div class="d-flex justify-content-between">

                            <div class="net-pay-label">
                                Net Pay
                            </div>

                            <div class="net-pay-value">
                                <?= $money($netPay) ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- PAYMENT SUMMARY -->
            <?php if (!empty($payments)): ?>

                <div class="section-title mt-5 mb-3">
                    Payment History
                </div>

                <div class="table-responsive">

                    <table class="table table-sm">

                        <thead>

                            <tr>

                                <th>Date</th>

                                <th>Method</th>

                                <th>Reference</th>

                                <th class="amount">
                                    Amount
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($payments as $payment): ?>

                                <tr>

                                    <td>
                                        <?= $datetime($payment['paid_at'] ?? null) ?>
                                    </td>

                                    <td>
                                        <?= $e(
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $payment['payment_method'] ?? 'other'
                                                )
                                            )
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $e(
                                            $payment['reference_number']
                                            ?? '—'
                                        ) ?>
                                    </td>

                                    <td class="amount">
                                        <?= $money($payment['amount'] ?? 0) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <div class="row justify-content-end">

                    <div class="col-md-5">

                        <table class="table table-sm">

                            <tr>

                                <td>
                                    Total Paid
                                </td>

                                <td class="amount fw-bold">
                                    <?= $money($totalPaid) ?>
                                </td>

                            </tr>

                            <tr>

                                <td>
                                    Remaining Balance
                                </td>

                                <td class="amount fw-bold">
                                    <?= $money($remainingBalance) ?>
                                </td>

                            </tr>

                        </table>

                    </div>

                </div>

            <?php endif; ?>

            <!-- SIGNATURES -->
            <div class="row mt-5">

                <div class="col-md-6">

                    <div class="signature-line">
                        Employee Signature
                    </div>

                </div>

                <div class="col-md-6 d-flex justify-content-md-end">

                    <div class="signature-line">
                        Authorized Signature
                    </div>

                </div>

            </div>

            <!-- FOOTER -->
            <div class="text-center text-muted small mt-5 pt-3 border-top">

                <?= $e(
                    $receiptFooter
                    ?? 'This is a computer-generated payslip.'
                ) ?>

            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>