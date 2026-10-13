<?php

$e = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$search       = $search ?? '';
$departmentId = (int) ($departmentId ?? 0);
$status        = $status ?? '';
$page         = (int) ($page ?? 1);
$perPage      = (int) ($perPage ?? 20);
$totalPages   = (int) ($totalPages ?? 1);
$totalEmployees = (int) ($totalEmployees ?? 0);

$statusLabels = [
    'active'     => 'Active',
    'inactive'   => 'Inactive',
    'on_leave'   => 'On Leave',
    'terminated' => 'Terminated'
];

$statusClasses = [
    'active'     => 'bg-success',
    'inactive'   => 'bg-secondary',
    'on_leave'   => 'bg-warning text-dark',
    'terminated' => 'bg-danger'
];

$buildUrl = static function (
    int $targetPage
) use (
    $search,
    $departmentId,
    $status
): string {

    $params = [];

    if ($search !== '') {
        $params['search'] = $search;
    }

    if ($departmentId > 0) {
        $params['department_id'] = $departmentId;
    }

    if ($status !== '') {
        $params['status'] = $status;
    }

    $params['page'] = $targetPage;

    return BASE_URL . '/schedules?' .
        http_build_query($params);
};

?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">
                <i data-lucide="calendar-days"></i>
                Employee Schedules
            </h1>

            <p class="text-muted mb-0">
                Manage employee weekly work schedules.
            </p>
        </div>

        <?php if (Auth::can('schedules.create')): ?>

            <a
                href="<?= BASE_URL ?>/schedules/create"
                class="btn btn-primary"
            >
                <i data-lucide="calendar-plus"></i>
                Create Weekly Schedule
            </a>

        <?php endif; ?>

    </div>


    <!-- Flash Messages -->

    <?php if (!empty($success)): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            <i data-lucide="circle-check"></i>
            <?= $e($success) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >
            <i data-lucide="circle-alert"></i>
            <?= $e($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>

    <?php endif; ?>


    <!-- Filters -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="<?= BASE_URL ?>/schedules"
            >

                <div class="row g-3 align-items-end">

                    <!-- Search -->

                    <div class="col-lg-5">

                        <label
                            for="search"
                            class="form-label fw-semibold"
                        >
                            Search Employee
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i data-lucide="search"></i>
                            </span>

                            <input
                                type="text"
                                name="search"
                                id="search"
                                class="form-control"
                                placeholder="Employee code or name..."
                                value="<?= $e($search) ?>"
                            >

                        </div>

                    </div>


                    <!-- Department -->

                    <div class="col-lg-3">

                        <label
                            for="department_id"
                            class="form-label fw-semibold"
                        >
                            Department
                        </label>

                        <select
                            name="department_id"
                            id="department_id"
                            class="form-select"
                        >

                            <option value="">
                                All Departments
                            </option>

                            <?php foreach ($departments as $department): ?>

                                <option
                                    value="<?= (int) $department['id'] ?>"
                                    <?= $departmentId === (int) $department['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= $e($department['name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Status -->

                    <div class="col-lg-2">

                        <label
                            for="status"
                            class="form-label fw-semibold"
                        >
                            Status
                        </label>

                        <select
                            name="status"
                            id="status"
                            class="form-select"
                        >

                            <option value="">
                                All Status
                            </option>

                            <?php foreach ($statusLabels as $key => $label): ?>

                                <option
                                    value="<?= $e($key) ?>"
                                    <?= $status === $key
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= $e($label) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Buttons -->

                    <div class="col-lg-2">

                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary flex-grow-1"
                            >
                                <i data-lucide="search"></i>
                                Search
                            </button>

                            <a
                                href="<?= BASE_URL ?>/schedules"
                                class="btn btn-outline-secondary"
                                title="Clear filters"
                            >
                                <i data-lucide="rotate-ccw"></i>
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Employee List -->

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <strong>
                        Employees
                    </strong>

                    <span class="text-muted ms-2">
                        <?= number_format($totalEmployees) ?>
                        employee(s)
                    </span>
                </div>

                <div class="text-muted small">
                    Page <?= $page ?> of <?= $totalPages ?>
                </div>

            </div>

        </div>


        <div class="card-body p-0">

            <?php if (empty($employees)): ?>

                <div class="text-center py-5">

                    <i
                        data-lucide="calendar-x"
                        style="width:48px;height:48px;"
                        class="text-muted mb-3"
                    ></i>

                    <h5>
                        No employees found
                    </h5>

                    <p class="text-muted mb-0">
                        Try changing your search or filters.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="px-4">
                                    Employee
                                </th>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Schedule
                                </th>

                                <th>
                                    Status
                                </th>

                                <th class="text-end px-4">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($employees as $employee): ?>

                                <?php

                                $employeeName = trim(
                                    ($employee['first_name'] ?? '') .
                                    ' ' .
                                    ($employee['middle_name'] ?? '') .
                                    ' ' .
                                    ($employee['last_name'] ?? '')
                                );

                                $scheduleCount =
                                    (int) ($employee['schedule_count'] ?? 0);

                                $workingDays =
                                    (int) ($employee['working_days'] ?? 0);

                                $restDays =
                                    (int) ($employee['rest_days'] ?? 0);

                                $employeeStatus =
                                    $employee['employment_status']
                                    ?? 'inactive';

                                ?>

                                <tr>

                                    <!-- Employee -->

                                    <td class="px-4">

                                        <div class="d-flex align-items-center gap-3">

                                            <div
                                                class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                style="width:42px;height:42px;"
                                            >
                                                <i data-lucide="user"></i>
                                            </div>

                                            <div>

                                                <div class="fw-semibold">
                                                    <?= $e($employeeName) ?>
                                                </div>

                                                <div class="small text-muted">
                                                    <?= $e($employee['employee_code']) ?>
                                                </div>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Department -->

                                    <td>

                                        <span>
                                            <?= $e(
                                                $employee['department_name']
                                                ?? '—'
                                            ) ?>
                                        </span>

                                    </td>


                                    <!-- Schedule -->

                                    <td>

                                        <?php if ($scheduleCount === 0): ?>

                                            <span class="badge bg-warning text-dark">
                                                <i data-lucide="calendar-x"></i>
                                                No Schedule
                                            </span>

                                        <?php else: ?>

                                            <div class="d-flex flex-wrap gap-1">

                                                <span class="badge bg-primary">
                                                    <?= $workingDays ?>
                                                    working
                                                </span>

                                                <?php if ($restDays > 0): ?>

                                                    <span class="badge bg-light text-dark border">
                                                        <?= $restDays ?>
                                                        rest
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                            <div class="small text-muted mt-1">
                                                <?= $scheduleCount ?>
                                                day(s) configured
                                            </div>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Status -->

                                    <td>

                                        <span
                                            class="badge <?= $e(
                                                $statusClasses[$employeeStatus]
                                                ?? 'bg-secondary'
                                            ) ?>"
                                        >
                                            <?= $e(
                                                $statusLabels[$employeeStatus]
                                                ?? ucfirst($employeeStatus)
                                            ) ?>
                                        </span>

                                    </td>


                                    <!-- Actions -->

                                    <td class="text-end px-4">

                                        <a
                                            href="<?= BASE_URL ?>/schedules/view/<?= (int) $employee['id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            <i data-lucide="calendar-search"></i>
                                            View Schedule
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>


        <!-- Pagination -->

        <?php if ($totalPages > 1): ?>

            <div class="card-footer bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <div class="small text-muted">

                        Showing
                        <?= number_format(
                            (($page - 1) * $perPage) + 1
                        ) ?>

                        to

                        <?= number_format(
                            min(
                                $page * $perPage,
                                $totalEmployees
                            )
                        ) ?>

                        of

                        <?= number_format($totalEmployees) ?>

                    </div>


                    <nav>

                        <ul class="pagination mb-0">

                            <!-- Previous -->

                            <li
                                class="page-item <?= $page <= 1
                                    ? 'disabled'
                                    : '' ?>"
                            >

                                <a
                                    class="page-link"
                                    href="<?= $page > 1
                                        ? $buildUrl($page - 1)
                                        : '#' ?>"
                                >
                                    <i data-lucide="chevron-left"></i>
                                </a>

                            </li>


                            <?php

                            $startPage = max(1, $page - 2);
                            $endPage = min(
                                $totalPages,
                                $page + 2
                            );

                            ?>

                            <?php for (
                                $i = $startPage;
                                $i <= $endPage;
                                $i++
                            ): ?>

                                <li
                                    class="page-item <?= $i === $page
                                        ? 'active'
                                        : '' ?>"
                                >

                                    <a
                                        class="page-link"
                                        href="<?= $buildUrl($i) ?>"
                                    >
                                        <?= $i ?>
                                    </a>

                                </li>

                            <?php endfor; ?>


                            <!-- Next -->

                            <li
                                class="page-item <?= $page >= $totalPages
                                    ? 'disabled'
                                    : '' ?>"
                            >

                                <a
                                    class="page-link"
                                    href="<?= $page < $totalPages
                                        ? $buildUrl($page + 1)
                                        : '#' ?>"
                                >
                                    <i data-lucide="chevron-right"></i>
                                </a>

                            </li>

                        </ul>

                    </nav>

                </div>

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