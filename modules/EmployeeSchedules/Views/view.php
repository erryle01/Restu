<?php

$e = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$days = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday'
];

$scheduleByDay = [];

foreach ($schedules as $schedule) {
    $scheduleByDay[
        (int) $schedule['day_of_week']
    ] = $schedule;
}

$employeeName = trim(
    ($employee['first_name'] ?? '') .
    ' ' .
    ($employee['middle_name'] ?? '') .
    ' ' .
    ($employee['last_name'] ?? '')
);

?>

<div class="container-fluid py-4">

    <!-- Header -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <div class="d-flex align-items-center gap-2 mb-2">

                <a
                    href="<?= BASE_URL ?>/schedules"
                    class="text-decoration-none"
                >
                    <i data-lucide="arrow-left"></i>
                </a>

                <span class="text-muted">
                    Employee Schedules
                </span>

            </div>

            <h1 class="h3 mb-1">

                <i data-lucide="calendar-days"></i>

                <?= $e($employeeName) ?>

            </h1>

            <p class="text-muted mb-0">

                <?= $e($employee['employee_code'] ?? '') ?>

                <?php if (!empty($employee['department_name'])): ?>

                    · <?= $e($employee['department_name']) ?>

                <?php endif; ?>

                <?php if (!empty($employee['position_name'])): ?>

                    · <?= $e($employee['position_name']) ?>

                <?php endif; ?>

            </p>

        </div>


        <div class="d-flex gap-2">

            <a
                href="<?= BASE_URL ?>/schedules"
                class="btn btn-outline-secondary"
            >
                <i data-lucide="arrow-left"></i>
                Back
            </a>

            <?php if (Auth::can('schedules.create')): ?>

                <a
                    href="<?= BASE_URL ?>/schedules/create"
                    class="btn btn-primary"
                >
                    <i data-lucide="calendar-plus"></i>
                    Create Schedule
                </a>

            <?php endif; ?>

        </div>

    </div>


    <!-- Messages -->

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


    <!-- Employee Summary -->

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center gap-3">

                        <div
                            class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                            style="width:48px;height:48px;"
                        >
                            <i data-lucide="user"></i>
                        </div>

                        <div>

                            <div class="small text-muted">
                                Employee
                            </div>

                            <div class="fw-semibold">
                                <?= $e($employeeName) ?>
                            </div>

                            <div class="small text-muted">
                                <?= $e($employee['employee_code'] ?? '') ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center gap-3">

                        <div
                            class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                            style="width:48px;height:48px;"
                        >
                            <i data-lucide="building-2"></i>
                        </div>

                        <div>

                            <div class="small text-muted">
                                Department
                            </div>

                            <div class="fw-semibold">
                                <?= $e(
                                    $employee['department_name']
                                    ?? '—'
                                ) ?>
                            </div>

                            <div class="small text-muted">
                                <?= $e(
                                    $employee['position_name']
                                    ?? '—'
                                ) ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center gap-3">

                        <div
                            class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                            style="width:48px;height:48px;"
                        >
                            <i data-lucide="calendar-check"></i>
                        </div>

                        <div>

                            <div class="small text-muted">
                                Configured Days
                            </div>

                            <div class="fw-semibold">
                                <?= count($schedules) ?>
                                / 7 days
                            </div>

                            <div class="small text-muted">
                                Weekly schedule
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Weekly Schedule -->

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">

            <div class="d-flex align-items-center gap-2">

                <i data-lucide="calendar-clock"></i>

                <strong>
                    Weekly Schedule
                </strong>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="px-4">
                                Day
                            </th>

                            <th>
                                Time In
                            </th>

                            <th>
                                Time Out
                            </th>

                            <th>
                                Break
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

                        <?php foreach ($days as $dayNumber => $dayName): ?>

                            <?php
                            $schedule =
                                $scheduleByDay[$dayNumber]
                                ?? null;
                            ?>

                            <tr>

                                <td class="px-4">

                                    <div class="fw-semibold">
                                        <?= $e($dayName) ?>
                                    </div>

                                </td>


                                <?php if (!$schedule): ?>

                                    <td colspan="4">

                                        <span class="text-muted">
                                            No schedule configured
                                        </span>

                                    </td>

                                    <td class="text-end px-4">

                                        <?php if (Auth::can('schedules.create')): ?>

                                            <a
                                                href="<?= BASE_URL ?>/schedules/create"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                <i data-lucide="plus"></i>
                                                Add
                                            </a>

                                        <?php endif; ?>

                                    </td>

                                <?php else: ?>

                                    <?php
                                    $isRestDay =
                                        (int) $schedule['is_rest_day'] === 1;
                                    ?>

                                    <td>

                                        <?php if ($isRestDay): ?>

                                            <span class="text-muted">
                                                —
                                            </span>

                                        <?php else: ?>

                                            <?= $e(
                                                date(
                                                    'h:i A',
                                                    strtotime(
                                                        $schedule['time_in']
                                                    )
                                                )
                                            ) ?>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?php if ($isRestDay): ?>

                                            <span class="text-muted">
                                                —
                                            </span>

                                        <?php else: ?>

                                            <?= $e(
                                                date(
                                                    'h:i A',
                                                    strtotime(
                                                        $schedule['time_out']
                                                    )
                                                )
                                            ) ?>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?php if ($isRestDay): ?>

                                            —

                                        <?php else: ?>

                                            <?= number_format(
                                                (int) $schedule['break_minutes']
                                            ) ?>
                                            min

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?php if ($isRestDay): ?>

                                            <span class="badge bg-secondary">
                                                <i data-lucide="moon"></i>
                                                Rest Day
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-success">
                                                <i data-lucide="briefcase"></i>
                                                Working
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td class="text-end px-4">

                                        <div class="d-flex justify-content-end gap-1">

                                            <?php if (Auth::can('schedules.edit')): ?>

                                                <a
                                                    href="<?= BASE_URL ?>/schedules/edit/<?= (int) $schedule['id'] ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Edit schedule"
                                                >
                                                    <i data-lucide="pencil"></i>
                                                </a>

                                            <?php endif; ?>


                                            <?php if (Auth::can('schedules.delete')): ?>

                                                <a
                                                    href="<?= BASE_URL ?>/schedules/delete/<?= (int) $schedule['id'] ?>"
                                                    class="btn btn-sm btn-outline-danger delete-schedule"
                                                    title="Delete schedule"
                                                >
                                                    <i data-lucide="trash-2"></i>
                                                </a>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                <?php endif; ?>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-schedule')
        .forEach(function (button) {

            button.addEventListener('click', function (event) {

                const confirmed = confirm(
                    'Are you sure you want to delete this schedule?'
                );

                if (!confirmed) {
                    event.preventDefault();
                }

            });

        });


    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

});
</script>