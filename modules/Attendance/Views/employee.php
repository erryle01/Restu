<?php
$e = static function ($value): string {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};

$employee = $employee ?? [];
$attendance = $attendance ?? [];
$summary = $summary ?? [];
$salary = $salary ?? [];

$employeeName = trim(
    ($employee['first_name'] ?? '') . ' ' .
    ($employee['middle_name'] ?? '') . ' ' .
    ($employee['last_name'] ?? '')
);

$dateFrom = $dateFrom ?? date('Y-m-01');
$dateTo = $dateTo ?? date('Y-m-d');

$statusLabels = [
    'present'  => 'Present',
    'late'     => 'Late',
    'absent'   => 'Absent',
    'half_day' => 'Half Day',
    'leave'    => 'Leave',
    'rest_day' => 'Rest Day',
    'holiday'  => 'Holiday'
];
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <div class="d-flex align-items-center gap-2">

                <i data-lucide="user-check"></i>

                <h1 class="h3 mb-0">
                    Attendance History
                </h1>

            </div>

            <p class="text-muted mb-0 mt-1">
                <?= $e($employeeName) ?>
                —
                <?= $e($employee['employee_code'] ?? '') ?>
            </p>

        </div>

        <div class="d-flex gap-2">

            <a
                href="<?= BASE_URL ?>/attendance"
                class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
            >
                <i data-lucide="arrow-left"></i>
                Attendance
            </a>

            <?php if (Auth::can('attendance.create') || Auth::can('attendance.manage')): ?>

                <a
                    href="<?= BASE_URL ?>/attendance/create"
                    class="btn btn-primary d-inline-flex align-items-center gap-2"
                >
                    <i data-lucide="plus"></i>
                    Record
                </a>

            <?php endif; ?>

        </div>

    </div>

    <!-- Employee Information -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-3">

                    <div class="text-muted small">
                        Employee Code
                    </div>

                    <div class="fw-semibold">
                        <?= $e($employee['employee_code'] ?? '') ?>
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="text-muted small">
                        Department
                    </div>

                    <div class="fw-semibold">
                        <?= $e($employee['department_name'] ?? '—') ?>
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="text-muted small">
                        Position
                    </div>

                    <div class="fw-semibold">
                        <?= $e($employee['position_name'] ?? '—') ?>
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="text-muted small">
                        Employment Status
                    </div>

                    <div class="fw-semibold">
                        <?= $e(ucwords(str_replace('_', ' ', $employee['employment_status'] ?? ''))) ?>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Salary Projection -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                <div>
                    <div class="text-muted small">
                        Automatic Salary Projection
                    </div>

                    <div class="fs-3 fw-bold text-success">
                        <?= number_format((float) ($salary['net_pay'] ?? 0), 2) ?>
                    </div>

                    <div class="text-muted small">
                        Net pay for <?= $e($dateFrom) ?> to <?= $e($dateTo) ?>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-4">

                    <div>
                        <div class="text-muted small">Basic Pay</div>
                        <div class="fw-semibold">
                            <?= number_format((float) ($salary['basic_pay'] ?? 0), 2) ?>
                        </div>
                    </div>

                    <div>
                        <div class="text-muted small">Overtime</div>
                        <div class="fw-semibold">
                            <?= number_format((float) ($salary['overtime_pay'] ?? 0), 2) ?>
                        </div>
                    </div>

                    <div>
                        <div class="text-muted small">Deductions</div>
                        <div class="fw-semibold text-danger">
                            <?= number_format((float) ($salary['total_deductions'] ?? 0), 2) ?>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="<?= BASE_URL ?>/attendance/view/<?= (int) $employee['id'] ?>"
            >

                <div class="row g-3 align-items-end">

                    <div class="col-md-4">

                        <label
                            for="date_from"
                            class="form-label"
                        >
                            Date From
                        </label>

                        <input
                            type="date"
                            id="date_from"
                            name="date_from"
                            class="form-control"
                            value="<?= $e($dateFrom) ?>"
                        >

                    </div>

                    <div class="col-md-4">

                        <label
                            for="date_to"
                            class="form-label"
                        >
                            Date To
                        </label>

                        <input
                            type="date"
                            id="date_to"
                            name="date_to"
                            class="form-control"
                            value="<?= $e($dateTo) ?>"
                        >

                    </div>

                    <div class="col-md-4">

                        <button
                            type="submit"
                            class="btn btn-primary d-inline-flex align-items-center gap-2"
                        >
                            <i data-lucide="filter"></i>
                            Apply
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- Summary -->
    <div class="row g-3 mb-4">

        <?php
        $summaryCards = [
            [
                'label' => 'Days Present',
                'value' => $summary['present_days'] ?? 0,
                'icon' => 'circle-check'
            ],
            [
                'label' => 'Late Days',
                'value' => $summary['late_days'] ?? 0,
                'icon' => 'clock-alert'
            ],
            [
                'label' => 'Absent Days',
                'value' => $summary['absent_days'] ?? 0,
                'icon' => 'circle-x'
            ],
            [
                'label' => 'Regular Hours',
                'value' => number_format(
                    (float) ($summary['regular_hours'] ?? 0),
                    2
                ) . ' h',
                'icon' => 'clock'
            ],
            [
                'label' => 'Overtime',
                'value' => number_format(
                    (float) ($summary['overtime_hours'] ?? 0),
                    2
                ) . ' h',
                'icon' => 'timer'
            ],
            [
                'label' => 'Late Minutes',
                'value' => ($summary['late_minutes'] ?? 0) . ' min',
                'icon' => 'alarm-clock'
            ]
        ];
        ?>

        <?php foreach ($summaryCards as $card): ?>

            <div class="col-md-4 col-xl-2">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="text-muted small mb-2">
                            <?= $e($card['label']) ?>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">

                            <div class="fs-5 fw-bold">
                                <?= $e($card['value']) ?>
                            </div>

                            <i data-lucide="<?= $e($card['icon']) ?>"></i>

                        </div>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

    <!-- History -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <div class="d-flex align-items-center gap-2">

                <i data-lucide="history"></i>

                <h5 class="mb-0">
                    Attendance Records
                </h5>

            </div>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="px-3">
                                Date
                            </th>

                            <th>
                                Schedule
                            </th>

                            <th>
                                Time In
                            </th>

                            <th>
                                Time Out
                            </th>

                            <th>
                                Regular
                            </th>

                            <th>
                                OT
                            </th>

                            <th>
                                Late
                            </th>

                            <th>
                                Undertime
                            </th>

                            <th>
                                Status
                            </th>

                            <th class="text-end px-3">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($attendance)): ?>

                            <tr>

                                <td
                                    colspan="10"
                                    class="text-center text-muted py-5"
                                >

                                    <i
                                        data-lucide="calendar-x"
                                        style="width:40px;height:40px;"
                                        class="mb-3"
                                    ></i>

                                    <div>
                                        No attendance records found.
                                    </div>

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach ($attendance as $row): ?>

                                <?php
                                $rowStatus = $row['status'] ?? 'present';

                                $statusClass = match ($rowStatus) {
                                    'present'  => 'success',
                                    'late'     => 'warning',
                                    'absent'   => 'danger',
                                    'half_day' => 'info',
                                    'leave'    => 'secondary',
                                    'rest_day' => 'dark',
                                    'holiday'  => 'primary',
                                    default    => 'secondary'
                                };
                                ?>

                                <tr>

                                    <td class="px-3">

                                        <div class="fw-semibold">
                                            <?= $e(
                                                date(
                                                    'M d, Y',
                                                    strtotime($row['attendance_date'])
                                                )
                                            ) ?>
                                        </div>

                                        <small class="text-muted">
                                            <?= $e(
                                                date(
                                                    'l',
                                                    strtotime($row['attendance_date'])
                                                )
                                            ) ?>
                                        </small>

                                    </td>

                                    <td>

                                        <?php if (
                                            !empty($row['scheduled_time_in']) &&
                                            !empty($row['scheduled_time_out'])
                                        ): ?>

                                            <?= $e(
                                                date(
                                                    'h:i A',
                                                    strtotime($row['scheduled_time_in'])
                                                )
                                            ) ?>

                                            -

                                            <?= $e(
                                                date(
                                                    'h:i A',
                                                    strtotime($row['scheduled_time_out'])
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php if (!empty($row['time_in'])): ?>

                                            <?= $e(
                                                date(
                                                    'h:i A',
                                                    strtotime($row['time_in'])
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php if (!empty($row['time_out'])): ?>

                                            <?= $e(
                                                date(
                                                    'h:i A',
                                                    strtotime($row['time_out'])
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?= number_format(
                                            (float) ($row['regular_hours'] ?? 0),
                                            2
                                        ) ?>
                                        h
                                    </td>

                                    <td>
                                        <?= number_format(
                                            (float) ($row['overtime_hours'] ?? 0),
                                            2
                                        ) ?>
                                        h
                                    </td>

                                    <td>
                                        <?= (int) ($row['late_minutes'] ?? 0) ?>
                                        min
                                    </td>

                                    <td>
                                        <?= (int) ($row['undertime_minutes'] ?? 0) ?>
                                        min
                                    </td>

                                    <td>

                                        <span class="badge text-bg-<?= $e($statusClass) ?>">
                                            <?= $e(
                                                $statusLabels[$rowStatus]
                                                ?? ucfirst($rowStatus)
                                            ) ?>
                                        </span>

                                    </td>

                                    <td class="text-end px-3">

                                        <div class="d-flex justify-content-end gap-1">

                                            <?php if (
                                                Auth::can('attendance.edit') ||
                                                Auth::can('attendance.manage')
                                            ): ?>

                                                <a
                                                    href="<?= BASE_URL ?>/attendance/edit/<?= (int) $row['id'] ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Edit"
                                                >
                                                    <i data-lucide="pencil"></i>
                                                </a>

                                            <?php endif; ?>

                                            <?php if (
                                                Auth::can('attendance.delete') ||
                                                Auth::can('attendance.manage')
                                            ): ?>

                                                <a
                                                    href="<?= BASE_URL ?>/attendance/delete/<?= (int) $row['id'] ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete"
                                                    onclick="return confirm('Delete this attendance record?');"
                                                >
                                                    <i data-lucide="trash-2"></i>
                                                </a>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

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