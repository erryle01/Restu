<?php
$e = static function ($value): string {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};

$success = $success ?? null;
$error = $error ?? null;

$dateFrom = $dateFrom ?? date('Y-m-01');
$dateTo = $dateTo ?? date('Y-m-d');
$employeeId = $employeeId ?? null;
$status = $status ?? '';
$page = $page ?? 1;
$perPage = $perPage ?? 20;
$totalPages = $totalPages ?? 1;
$totalRecords = $totalRecords ?? 0;
$attendance = $attendance ?? [];
$employees = $employees ?? [];

$statusLabels = [
    'present'  => 'Present',
    'late'     => 'Late',
    'absent'   => 'Absent',
    'half_day' => 'Half Day',
    'leave'    => 'Leave',
    'rest_day' => 'Rest Day',
    'holiday'  => 'Holiday'
];

$statusIcons = [
    'present'  => 'circle-check',
    'late'     => 'clock-alert',
    'absent'   => 'circle-x',
    'half_day' => 'circle-minus',
    'leave'    => 'calendar-off',
    'rest_day' => 'bed',
    'holiday'  => 'calendar-days'
];
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <div class="d-flex align-items-center gap-2">
                <i data-lucide="clipboard-check"></i>

                <h1 class="h3 mb-0">
                    Attendance
                </h1>
            </div>

            <p class="text-muted mb-0 mt-1">
                Manage employee attendance, time in, time out and working hours.
            </p>
        </div>

        <?php if (Auth::can('attendance.create') || Auth::can('attendance.manage')): ?>
            <a
                href="<?= BASE_URL ?>/attendance/create"
                class="btn btn-primary d-inline-flex align-items-center gap-2"
            >
                <i data-lucide="plus"></i>
                Record Attendance
            </a>
        <?php endif; ?>

    </div>

    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-lucide="circle-check" class="me-2"></i>
            <?= $e($success) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-lucide="circle-alert" class="me-2"></i>
            <?= $e($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="<?= BASE_URL ?>/attendance"
            >

                <div class="row g-3 align-items-end">

                    <!-- Date From -->
                    <div class="col-md-3">

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

                    <!-- Date To -->
                    <div class="col-md-3">

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

                    <!-- Employee -->
                    <div class="col-md-3">

                        <label
                            for="employee_id"
                            class="form-label"
                        >
                            Employee
                        </label>

                        <select
                            id="employee_id"
                            name="employee_id"
                            class="form-select"
                        >

                            <option value="">
                                All Employees
                            </option>

                            <?php foreach ($employees as $employee): ?>

                                <?php
                                $fullName = trim(
                                    ($employee['first_name'] ?? '') . ' ' .
                                    ($employee['middle_name'] ?? '') . ' ' .
                                    ($employee['last_name'] ?? '')
                                );
                                ?>

                                <option
                                    value="<?= (int) $employee['id'] ?>"
                                    <?= ((int) $employeeId === (int) $employee['id']) ? 'selected' : '' ?>
                                >
                                    <?= $e($employee['employee_code']) ?>
                                    -
                                    <?= $e($fullName) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- Status -->
                    <div class="col-md-3">

                        <label
                            for="status"
                            class="form-label"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All Status
                            </option>

                            <?php foreach ($statusLabels as $value => $label): ?>

                                <option
                                    value="<?= $e($value) ?>"
                                    <?= $status === $value ? 'selected' : '' ?>
                                >
                                    <?= $e($label) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- Buttons -->
                    <div class="col-12">

                        <div class="d-flex flex-wrap gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary d-inline-flex align-items-center gap-2"
                            >
                                <i data-lucide="search"></i>
                                Filter
                            </button>

                            <a
                                href="<?= BASE_URL ?>/attendance"
                                class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
                            >
                                <i data-lucide="rotate-ccw"></i>
                                Reset
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- Summary -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between">

                        <div>
                            <div class="text-muted small">
                                Attendance Records
                            </div>

                            <div class="fs-3 fw-bold">
                                <?= number_format((int) $totalRecords) ?>
                            </div>
                        </div>

                        <div class="text-primary">
                            <i data-lucide="clipboard-list"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between">

                        <div>
                            <div class="text-muted small">
                                Period
                            </div>

                            <div class="fw-semibold">
                                <?= $e($dateFrom) ?>
                                →
                                <?= $e($dateTo) ?>
                            </div>
                        </div>

                        <div class="text-primary">
                            <i data-lucide="calendar-range"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between">

                        <div>
                            <div class="text-muted small">
                                Page
                            </div>

                            <div class="fs-5 fw-bold">
                                <?= (int) $page ?>
                                /
                                <?= (int) max(1, $totalPages) ?>
                            </div>
                        </div>

                        <div class="text-primary">
                            <i data-lucide="files"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Attendance Table -->
    <div class="card border-0 shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="px-3">
                                Date
                            </th>

                            <th>
                                Employee
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
                                    class="text-center py-5 text-muted"
                                >

                                    <i
                                        data-lucide="clipboard-x"
                                        style="width:40px;height:40px;"
                                        class="mb-3"
                                    ></i>

                                    <div class="fw-semibold">
                                        No attendance records found.
                                    </div>

                                    <small>
                                        Try changing your filters or create a new attendance record.
                                    </small>

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach ($attendance as $row): ?>

                                <?php
                                $rowStatus = $row['status'] ?? 'present';

                                $employeeName = trim(
                                    ($row['first_name'] ?? '') . ' ' .
                                    ($row['middle_name'] ?? '') . ' ' .
                                    ($row['last_name'] ?? '')
                                );

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
                                            <?= $e(date('M d, Y', strtotime($row['attendance_date']))) ?>
                                        </div>

                                        <small class="text-muted">
                                            <?= $e(date('l', strtotime($row['attendance_date']))) ?>
                                        </small>

                                    </td>

                                    <td>

                                        <div class="fw-semibold">
                                            <?= $e($employeeName) ?>
                                        </div>

                                        <small class="text-muted">
                                            <?= $e($row['employee_code'] ?? '') ?>
                                        </small>

                                    </td>

                                    <td>

                                        <?php if (!empty($row['scheduled_time_in']) && !empty($row['scheduled_time_out'])): ?>

                                            <span class="small">
                                                <?= $e(date('h:i A', strtotime($row['scheduled_time_in']))) ?>
                                                -
                                                <?= $e(date('h:i A', strtotime($row['scheduled_time_out']))) ?>
                                            </span>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                No schedule
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php if (!empty($row['time_in'])): ?>

                                            <?= $e(date('h:i A', strtotime($row['time_in']))) ?>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php if (!empty($row['time_out'])): ?>

                                            <?= $e(date('h:i A', strtotime($row['time_out']))) ?>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?= number_format((float) ($row['regular_hours'] ?? 0), 2) ?> h
                                    </td>

                                    <td>
                                        <?= number_format((float) ($row['overtime_hours'] ?? 0), 2) ?> h
                                    </td>

                                    <td>

                                        <?php
                                        $lateMinutes = (int) ($row['late_minutes'] ?? 0);
                                        ?>

                                        <?php if ($lateMinutes > 0): ?>

                                            <span class="text-warning fw-semibold">
                                                <?= $lateMinutes ?> min
                                            </span>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <span class="badge text-bg-<?= $e($statusClass) ?> d-inline-flex align-items-center gap-1">

                                            <i
                                                data-lucide="<?= $e($statusIcons[$rowStatus] ?? 'circle') ?>"
                                                style="width:14px;height:14px;"
                                            ></i>

                                            <?= $e($statusLabels[$rowStatus] ?? ucfirst($rowStatus)) ?>

                                        </span>

                                    </td>

                                    <td class="text-end px-3">

                                        <div class="d-flex justify-content-end gap-1">

                                            <a
                                                href="<?= BASE_URL ?>/attendance/view/<?= (int) $row['employee_id'] ?>"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Employee attendance"
                                            >
                                                <i data-lucide="history"></i>
                                            </a>

                                            <?php if (Auth::can('attendance.edit') || Auth::can('attendance.manage')): ?>

                                                <a
                                                    href="<?= BASE_URL ?>/attendance/edit/<?= (int) $row['id'] ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Edit"
                                                >
                                                    <i data-lucide="pencil"></i>
                                                </a>

                                            <?php endif; ?>

                                            <?php if (Auth::can('attendance.delete') || Auth::can('attendance.manage')): ?>

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

        <?php if ($totalPages > 1): ?>

            <div class="card-footer bg-white">

                <?php
                $query = [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'employee_id' => $employeeId,
                    'status' => $status
                ];
                ?>

                <nav>

                    <ul class="pagination mb-0 justify-content-end">

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                            <?php
                            $query['page'] = $i;
                            $url = BASE_URL . '/attendance?' . http_build_query($query);
                            ?>

                            <li class="page-item <?= $i === (int) $page ? 'active' : '' ?>">

                                <a
                                    class="page-link"
                                    href="<?= $e($url) ?>"
                                >
                                    <?= $i ?>
                                </a>

                            </li>

                        <?php endfor; ?>

                    </ul>

                </nav>

            </div>

        <?php endif; ?>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>