
<?php
$e = static fn($value) =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$statusClass = [
    'draft'      => 'secondary',
    'processing' => 'warning',
    'review'     => 'info',
    'approved'   => 'primary',
    'paid'       => 'success',
    'locked'     => 'dark'
];

$status = $period['status'];
?>

<div class="container-fluid py-4">

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i data-lucide="circle-check" class="me-2"></i>
            <?= $e($success) ?>
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i data-lucide="circle-alert" class="me-2"></i>
            <?= $e($error) ?>
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>
        </div>
    <?php endif; ?>

    <!-- Header -->

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">

        <div class="d-flex align-items-start gap-3">

            <a
                href="<?= BASE_URL ?>/payroll"
                class="btn btn-outline-secondary">
                <i data-lucide="arrow-left"></i>
            </a>

            <div>

                <div class="d-flex align-items-center gap-2 flex-wrap">

                    <h2 class="fw-bold mb-1">
                        <?= $e($period['period_name']) ?>
                    </h2>

                    <span class="badge text-bg-<?= $statusClass[$status] ?? 'secondary' ?>">
                        <?= $e(ucfirst($status)) ?>
                    </span>

                </div>

                <p class="text-muted mb-0">
                    <?= $e($period['start_date']) ?>
                    →
                    <?= $e($period['end_date']) ?>
                </p>

            </div>

        </div>

        <div class="d-flex flex-wrap gap-2">

            <?php if (
                $status === 'draft'
                && Auth::can('payroll.create')
            ): ?>

                <a
                    href="<?= BASE_URL ?>/payroll/edit/<?= (int) $period['id'] ?>"
                    class="btn btn-outline-secondary">
                    <i data-lucide="pencil" class="me-1"></i>
                    Edit
                </a>

            <?php endif; ?>

            <?php if (
                in_array($status, ['draft', 'processing', 'review'], true)
                && Auth::can('payroll.calculate')
            ): ?>

                <form
                    method="POST"
                    action="<?= BASE_URL ?>/payroll/calculate/<?= (int) $period['id'] ?>"
                    class="d-inline"
                    onsubmit="return confirm('Recalculate this payroll? Existing calculated payroll records will be refreshed.');">

                    <button
                        type="submit"
                        class="btn btn-primary">
                        <i data-lucide="calculator" class="me-1"></i>
                        Calculate Payroll
                    </button>

                </form>

            <?php endif; ?>

            <?php if (
                $status === 'review'
                && Auth::can('payroll.approve')
            ): ?>

                <form
                    method="POST"
                    action="<?= BASE_URL ?>/payroll/approve/<?= (int) $period['id'] ?>"
                    class="d-inline"
                    onsubmit="return confirm('Approve this payroll?');">

                    <button
                        type="submit"
                        class="btn btn-success">
                        <i data-lucide="badge-check" class="me-1"></i>
                        Approve Payroll
                    </button>

                </form>

            <?php endif; ?>

        </div>

    </div>

    <!-- Period summary -->

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <small class="text-muted">
                        Employees
                    </small>

                    <h3 class="fw-bold mt-2 mb-0">
                        <?= count($payroll) ?>
                    </h3>

                </div>
            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <small class="text-muted">
                        Total Gross
                    </small>

                    <h3 class="fw-bold mt-2 mb-0">
                        ₱<?= number_format(
                            array_sum(
                                array_column(
                                    $payroll,
                                    'gross_pay'
                                )
                            ),
                            2
                        ) ?>
                    </h3>

                </div>
            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <small class="text-muted">
                        Total Deductions
                    </small>

                    <h3 class="fw-bold mt-2 mb-0">
                        ₱<?= number_format(
                            array_sum(
                                array_column(
                                    $payroll,
                                    'total_deductions'
                                )
                            ),
                            2
                        ) ?>
                    </h3>

                </div>
            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <small class="text-muted">
                        Total Net Pay
                    </small>

                    <h3 class="fw-bold mt-2 mb-0 text-success">
                        ₱<?= number_format(
                            array_sum(
                                array_column(
                                    $payroll,
                                    'net_pay'
                                )
                            ),
                            2
                        ) ?>
                    </h3>

                </div>
            </div>

        </div>

    </div>

    <!-- Payroll records -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-0">
                    Employee Payroll
                </h5>

                <span class="text-muted small">
                    <?= count($payroll) ?> employee(s)
                </span>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Basic Pay</th>
                        <th>OT Pay</th>
                        <th>Gross Pay</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (empty($payroll)): ?>

                    <tr>
                        <td colspan="9" class="text-center py-5">

                            <i
                                data-lucide="calculator"
                                style="width:42px;height:42px"
                                class="text-muted mb-3">
                            </i>

                            <h6 class="fw-bold">
                                No payroll calculated
                            </h6>

                            <p class="text-muted mb-0">
                                Calculate the payroll period to generate
                                employee payroll records.
                            </p>

                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($payroll as $row): ?>

                        <tr>

                            <td>

                                <div class="fw-semibold">

                                    <?= $e(
                                        $row['last_name']
                                        . ', '
                                        . $row['first_name']
                                        . (
                                            !empty($row['middle_name'])
                                            ? ' ' . $row['middle_name']
                                            : ''
                                        )
                                    ) ?>

                                </div>

                                <small class="text-muted">
                                    <?= $e($row['employee_code']) ?>
                                </small>

                            </td>

                            <td>
                                <?= $e(
                                    $row['department_name']
                                    ?? '—'
                                ) ?>
                            </td>

                            <td>
                                ₱<?= number_format(
                                    (float) $row['basic_pay'],
                                    2
                                ) ?>
                            </td>

                            <td>
                                ₱<?= number_format(
                                    (float) $row['overtime_pay'],
                                    2
                                ) ?>
                            </td>

                            <td class="fw-semibold">
                                ₱<?= number_format(
                                    (float) $row['gross_pay'],
                                    2
                                ) ?>
                            </td>

                            <td>
                                ₱<?= number_format(
                                    (float) $row['total_deductions'],
                                    2
                                ) ?>
                            </td>

                            <td class="fw-bold text-success">
                                ₱<?= number_format(
                                    (float) $row['net_pay'],
                                    2
                                ) ?>
                            </td>

                            <td>

                                <span class="badge text-bg-<?=
                                    $statusClass[
                                        $row['status']
                                    ] ?? 'secondary'
                                ?>">
                                    <?= $e(
                                        ucfirst($row['status'])
                                    ) ?>
                                </span>

                            </td>

                            <td class="text-end">

                                <a
                                    href="<?= BASE_URL ?>/payroll/payslip/<?= (int) $row['id'] ?>"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Payslip">

                                    <i data-lucide="receipt-text"></i>

                                </a>

                                <?php if (
                                    $row['status'] === 'approved'
                                    && Auth::can('payroll.pay')
                                ): ?>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-success"
                                        data-bs-toggle="modal"
                                        data-bs-target="#paymentModal<?= (int) $row['id'] ?>"
                                        title="Pay">

                                        <i data-lucide="banknote"></i>

                                    </button>

                                <?php endif; ?>

                            </td>

                        </tr>

                        <!-- Payment Modal -->

                        <?php if (
                            $row['status'] === 'approved'
                            && Auth::can('payroll.pay')
                        ): ?>

                            <div
                                class="modal fade"
                                id="paymentModal<?= (int) $row['id'] ?>"
                                tabindex="-1">

                                <div class="modal-dialog">

                                    <div class="modal-content">

                                        <div class="modal-header">

                                            <h5 class="modal-title">
                                                Record Payroll Payment
                                            </h5>

                                            <button
                                                type="button"
                                                class="btn-close"
                                                data-bs-dismiss="modal">
                                            </button>

                                        </div>

                                        <form
                                            method="POST"
                                            action="<?= BASE_URL ?>/payroll/pay/<?= (int) $row['id'] ?>">

                                            <div class="modal-body">

                                                <div class="mb-3">

                                                    <label class="form-label">
                                                        Employee
                                                    </label>

                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        value="<?= $e(
                                                            $row['last_name']
                                                            . ', '
                                                            . $row['first_name']
                                                        ) ?>"
                                                        readonly>

                                                </div>

                                                <div class="mb-3">

                                                    <label class="form-label">
                                                        Net Pay
                                                    </label>

                                                    <input
                                                        type="text"
                                                        class="form-control fw-bold"
                                                        value="₱<?= number_format(
                                                            (float) $row['net_pay'],
                                                            2
                                                        ) ?>"
                                                        readonly>

                                                </div>

                                                <div class="mb-3">

                                                    <label class="form-label fw-semibold">
                                                        Payment Method
                                                    </label>

                                                    <select
                                                        name="payment_method"
                                                        class="form-select"
                                                        required>

                                                        <option value="">
                                                            Select method
                                                        </option>

                                                        <option value="cash">
                                                            Cash
                                                        </option>

                                                        <option value="bank_transfer">
                                                            Bank Transfer
                                                        </option>

                                                        <option value="gcash">
                                                            GCash
                                                        </option>

                                                        <option value="other">
                                                            Other
                                                        </option>

                                                    </select>

                                                </div>

                                                <div class="mb-3">

                                                    <label class="form-label">
                                                        Reference Number
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="reference_number"
                                                        class="form-control"
                                                        maxlength="100"
                                                        placeholder="Optional">

                                                </div>

                                            </div>

                                            <div class="modal-footer">

                                                <button
                                                    type="button"
                                                    class="btn btn-light border"
                                                    data-bs-dismiss="modal">
                                                    Cancel
                                                </button>

                                                <button
                                                    type="submit"
                                                    class="btn btn-success">
                                                    <i data-lucide="check"></i>
                                                    Record Payment
                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

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

