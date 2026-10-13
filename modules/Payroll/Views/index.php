
<?php
$e = static fn($value) =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$statusBadge = static function (string $status): string {
    $map = [
        'draft'      => 'secondary',
        'processing' => 'warning',
        'review'     => 'info',
        'approved'   => 'primary',
        'paid'       => 'success',
        'locked'     => 'dark'
    ];

    $class = $map[$status] ?? 'secondary';

    return '<span class="badge text-bg-' .
        $class . '">' .
        htmlspecialchars(
            ucfirst($status),
            ENT_QUOTES,
            'UTF-8'
        ) .
        '</span>';
};
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

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">

        <div>
            <h2 class="fw-bold mb-1">
                <i data-lucide="wallet-cards" class="me-2"></i>
                Payroll
            </h2>

            <p class="text-muted mb-0">
                Manage payroll periods, calculations, approvals and payments.
            </p>
        </div>

        <?php if (Auth::can('payroll.create')): ?>
            <a
                href="<?= BASE_URL ?>/payroll/create"
                class="btn btn-primary">
                <i data-lucide="plus" class="me-1"></i>
                New Payroll Period
            </a>
        <?php endif; ?>

    </div>

    <!-- Summary -->
    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">
                                Payroll Periods
                            </small>

                            <h3 class="fw-bold mt-2 mb-0">
                                <?= count($periods) ?>
                            </h3>
                        </div>

                        <div class="text-primary">
                            <i data-lucide="calendar-range"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">
                                Under Review
                            </small>

                            <h3 class="fw-bold mt-2 mb-0">
                                <?=
                                    count(array_filter(
                                        $periods,
                                        static fn($p) =>
                                            $p['status'] === 'review'
                                    ))
                                ?>
                            </h3>
                        </div>

                        <div class="text-info">
                            <i data-lucide="clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">
                                Approved
                            </small>

                            <h3 class="fw-bold mt-2 mb-0">
                                <?=
                                    count(array_filter(
                                        $periods,
                                        static fn($p) =>
                                            $p['status'] === 'approved'
                                    ))
                                ?>
                            </h3>
                        </div>

                        <div class="text-success">
                            <i data-lucide="badge-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">
                                Paid
                            </small>

                            <h3 class="fw-bold mt-2 mb-0">
                                <?=
                                    count(array_filter(
                                        $periods,
                                        static fn($p) =>
                                            $p['status'] === 'paid'
                                    ))
                                ?>
                            </h3>
                        </div>

                        <div class="text-success">
                            <i data-lucide="circle-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Payroll periods -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">
                    Payroll Periods
                </h5>

                <span class="text-muted small">
                    <?= count($periods) ?> record(s)
                </span>
            </div>
        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>
                        <th>Period</th>
                        <th>Date Range</th>
                        <th>Pay Date</th>
                        <th>Employees</th>
                        <th>Gross</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (empty($periods)): ?>

                    <tr>
                        <td colspan="9" class="text-center py-5">

                            <i
                                data-lucide="wallet-cards"
                                style="width:42px;height:42px"
                                class="text-muted mb-3">
                            </i>

                            <h6 class="fw-bold">
                                No payroll periods yet
                            </h6>

                            <p class="text-muted mb-3">
                                Create your first payroll period to begin.
                            </p>

                            <?php if (Auth::can('payroll.create')): ?>
                                <a
                                    href="<?= BASE_URL ?>/payroll/create"
                                    class="btn btn-primary btn-sm">
                                    <i data-lucide="plus" class="me-1"></i>
                                    Create Payroll
                                </a>
                            <?php endif; ?>

                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($periods as $period): ?>

                        <tr>

                            <td>
                                <div class="fw-semibold">
                                    <?= $e($period['period_name']) ?>
                                </div>

                                <small class="text-muted">
                                    #<?= (int) $period['id'] ?>
                                </small>
                            </td>

                            <td>
                                <?= $e($period['start_date']) ?>
                                <span class="text-muted">→</span>
                                <?= $e($period['end_date']) ?>
                            </td>

                            <td>
                                <?= $period['pay_date']
                                    ? $e($period['pay_date'])
                                    : '<span class="text-muted">Not set</span>' ?>
                            </td>

                            <td>
                                <span class="badge text-bg-light border">
                                    <?= (int) $period['employee_count'] ?>
                                </span>
                            </td>

                            <td>
                                ₱<?= number_format(
                                    (float) $period['total_gross'],
                                    2
                                ) ?>
                            </td>

                            <td>
                                ₱<?= number_format(
                                    (float) $period['total_deductions'],
                                    2
                                ) ?>
                            </td>

                            <td class="fw-semibold">
                                ₱<?= number_format(
                                    (float) $period['total_net'],
                                    2
                                ) ?>
                            </td>

                            <td>
                                <?= $statusBadge($period['status']) ?>
                            </td>

                            <td class="text-end">

                                <a
                                    href="<?= BASE_URL ?>/payroll/view/<?= (int) $period['id'] ?>"
                                    class="btn btn-sm btn-outline-primary"
                                    title="View">
                                    <i data-lucide="eye"></i>
                                </a>

                                <?php if (
                                    $period['status'] === 'draft'
                                    && Auth::can('payroll.create')
                                ): ?>

                                    <a
                                        href="<?= BASE_URL ?>/payroll/edit/<?= (int) $period['id'] ?>"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Edit">
                                        <i data-lucide="pencil"></i>
                                    </a>

                                <?php endif; ?>

                            </td>

                        </tr>

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

