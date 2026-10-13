
<?php
/**
 * Employee Schedules - Edit
 *
 * Expected variables:
 * $schedule
 * $employees
 * $error
 */

$e = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$error = $error ?? null;

$currentEmployeeId = (int) ($schedule['employee_id'] ?? 0);
$currentDay        = (int) ($schedule['day_of_week'] ?? 1);
$currentTimeIn     = $schedule['time_in'] ?? '08:00:00';
$currentTimeOut    = $schedule['time_out'] ?? '17:00:00';
$currentBreak      = (int) ($schedule['break_minutes'] ?? 60);
$currentRestDay    = (int) ($schedule['is_rest_day'] ?? 0) === 1;

$days = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday'
];
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">
                <i data-lucide="calendar-clock"></i>
                Edit Employee Schedule
            </h1>

            <p class="text-muted mb-0">
                Update the employee's weekly schedule.
            </p>
        </div>

        <a
            href="<?= BASE_URL ?>/schedules"
            class="btn btn-outline-secondary"
        >
            <i data-lucide="arrow-left"></i>
            Back to Schedules
        </a>

    </div>


    <!-- Error -->
    <?php if (!empty($error)): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >
            <div class="d-flex align-items-center gap-2">

                <i data-lucide="circle-alert"></i>

                <div>
                    <?= $e($error) ?>
                </div>

            </div>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>

    <?php endif; ?>


    <!-- Schedule Card -->
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">

            <div class="d-flex align-items-center gap-2">

                <i data-lucide="calendar-cog"></i>

                <strong>
                    Schedule Information
                </strong>

            </div>

        </div>


        <div class="card-body">

            <form
                method="POST"
                action="<?= BASE_URL ?>/schedules/edit/<?= (int) $schedule['id'] ?>"
                id="scheduleForm"
            >

                <!-- Employee -->
                <div class="row g-3">

                    <div class="col-md-6">

                        <label
                            for="employee_id"
                            class="form-label fw-semibold"
                        >
                            Employee
                        </label>

                        <select
                            name="employee_id"
                            id="employee_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select employee
                            </option>

                            <?php foreach ($employees as $employee): ?>

                                <?php
                                $employeeId = (int) $employee['id'];

                                $employeeName =
                                    trim(
                                        ($employee['first_name'] ?? '') .
                                        ' ' .
                                        ($employee['middle_name'] ?? '') .
                                        ' ' .
                                        ($employee['last_name'] ?? '')
                                    );

                                $selected =
                                    $employeeId === $currentEmployeeId;
                                ?>

                                <option
                                    value="<?= $employeeId ?>"
                                    <?= $selected ? 'selected' : '' ?>
                                >
                                    <?= $e($employee['employee_code'] ?? '') ?>
                                    -
                                    <?= $e($employeeName) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <div class="form-text">
                            Select the employee assigned to this schedule.
                        </div>

                    </div>


                    <!-- Day -->
                    <div class="col-md-6">

                        <label
                            for="day_of_week"
                            class="form-label fw-semibold"
                        >
                            Day of Week
                        </label>

                        <select
                            name="day_of_week"
                            id="day_of_week"
                            class="form-select"
                            required
                        >

                            <?php foreach ($days as $dayNumber => $dayName): ?>

                                <option
                                    value="<?= $dayNumber ?>"
                                    <?= $currentDay === $dayNumber ? 'selected' : '' ?>
                                >
                                    <?= $e($dayName) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <div class="form-text">
                            Each employee can have one schedule per day.
                        </div>

                    </div>

                </div>


                <hr class="my-4">


                <!-- Working Schedule -->
                <div class="row g-3">

                    <!-- Time In -->
                    <div class="col-md-4">

                        <label
                            for="time_in"
                            class="form-label fw-semibold"
                        >
                            Time In
                        </label>

                        <input
                            type="time"
                            name="time_in"
                            id="time_in"
                            class="form-control"
                            value="<?= $e(substr($currentTimeIn, 0, 5)) ?>"
                            required
                        >

                    </div>


                    <!-- Time Out -->
                    <div class="col-md-4">

                        <label
                            for="time_out"
                            class="form-label fw-semibold"
                        >
                            Time Out
                        </label>

                        <input
                            type="time"
                            name="time_out"
                            id="time_out"
                            class="form-control"
                            value="<?= $e(substr($currentTimeOut, 0, 5)) ?>"
                            required
                        >

                    </div>


                    <!-- Break -->
                    <div class="col-md-4">

                        <label
                            for="break_minutes"
                            class="form-label fw-semibold"
                        >
                            Break Minutes
                        </label>

                        <input
                            type="number"
                            name="break_minutes"
                            id="break_minutes"
                            class="form-control"
                            min="0"
                            max="1440"
                            value="<?= $currentBreak ?>"
                            required
                        >

                        <div class="form-text">
                            Example: 60 = 1 hour.
                        </div>

                    </div>

                </div>


                <hr class="my-4">


                <!-- Rest Day -->
                <div class="form-check form-switch mb-4">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        name="is_rest_day"
                        id="is_rest_day"
                        value="1"
                        <?= $currentRestDay ? 'checked' : '' ?>
                    >

                    <label
                        class="form-check-label fw-semibold"
                        for="is_rest_day"
                    >
                        Rest Day
                    </label>

                    <div class="form-text">
                        Enable this if the employee is not scheduled to work
                        on this day.
                    </div>

                </div>


                <!-- Buttons -->
                <div class="d-flex justify-content-end gap-2">

                    <a
                        href="<?= BASE_URL ?>/schedules"
                        class="btn btn-light"
                    >
                        <i data-lucide="x"></i>
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i data-lucide="save"></i>
                        Update Schedule
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const restDayCheckbox = document.getElementById('is_rest_day');
    const timeIn          = document.getElementById('time_in');
    const timeOut         = document.getElementById('time_out');
    const breakMinutes    = document.getElementById('break_minutes');
    const form             = document.getElementById('scheduleForm');

    function updateRestDayState() {

        const isRestDay = restDayCheckbox.checked;

        timeIn.disabled       = isRestDay;
        timeOut.disabled      = isRestDay;
        breakMinutes.disabled = isRestDay;

        if (isRestDay) {

            timeIn.removeAttribute('required');
            timeOut.removeAttribute('required');

            breakMinutes.value = 0;

        } else {

            timeIn.setAttribute('required', 'required');
            timeOut.setAttribute('required', 'required');

            if (
                !breakMinutes.value ||
                parseInt(breakMinutes.value, 10) < 0
            ) {
                breakMinutes.value = 60;
            }
        }
    }


    restDayCheckbox.addEventListener(
        'change',
        updateRestDayState
    );


    form.addEventListener('submit', function (event) {

        if (restDayCheckbox.checked) {
            return;
        }

        if (!timeIn.value || !timeOut.value) {

            event.preventDefault();

            alert('Please enter both Time In and Time Out.');

            return;
        }


        if (timeOut.value <= timeIn.value) {

            event.preventDefault();

            alert('Time Out must be later than Time In.');

            return;
        }


        const breakValue =
            parseInt(breakMinutes.value, 10);

        if (
            Number.isNaN(breakValue) ||
            breakValue < 0 ||
            breakValue > 1440
        ) {

            event.preventDefault();

            alert(
                'Break minutes must be between 0 and 1440.'
            );

            return;
        }

    });


    updateRestDayState();


    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

});
</script>

