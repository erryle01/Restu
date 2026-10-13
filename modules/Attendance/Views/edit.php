<?php
$e = static function ($value): string {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};

$attendance = $attendance ?? [];
$error = $error ?? null;

$employeeId = $_POST['employee_id'] ?? ($attendance['employee_id'] ?? '');
$date = $_POST['attendance_date'] ?? ($attendance['attendance_date'] ?? date('Y-m-d'));

$timeIn = $_POST['time_in'] ?? (
    !empty($attendance['time_in'])
        ? date('H:i', strtotime($attendance['time_in']))
        : ''
);

$timeOut = $_POST['time_out'] ?? (
    !empty($attendance['time_out'])
        ? date('H:i', strtotime($attendance['time_out']))
        : ''
);

$breakMinutes = $_POST['break_minutes'] ?? ($attendance['break_minutes'] ?? 0);
$status = $_POST['status'] ?? ($attendance['status'] ?? 'present');
$notes = $_POST['notes'] ?? ($attendance['notes'] ?? '');

$employeeName = trim(
    ($attendance['first_name'] ?? '') . ' ' .
    ($attendance['middle_name'] ?? '') . ' ' .
    ($attendance['last_name'] ?? '')
);
?>

<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <div class="d-flex align-items-center gap-2">

                <i data-lucide="clipboard-pen"></i>

                <h1 class="h3 mb-0">
                    Edit Attendance
                </h1>

            </div>

            <p class="text-muted mb-0 mt-1">
                Update attendance information and working hours.
            </p>

        </div>

        <a
            href="<?= BASE_URL ?>/attendance"
            class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
        >
            <i data-lucide="arrow-left"></i>
            Back
        </a>

    </div>

    <?php if ($error): ?>

        <div class="alert alert-danger">
            <i data-lucide="circle-alert" class="me-2"></i>
            <?= $e($error) ?>
        </div>

    <?php endif; ?>

    <div class="row">

        <div class="col-xl-8">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <form
                        method="POST"
                        action="<?= BASE_URL ?>/attendance/edit/<?= (int) $attendance['id'] ?>"
                        id="attendanceForm"
                    >

                        <div class="row g-3">

                            <!-- Employee -->
                            <div class="col-md-6">

                                <label class="form-label">
                                    Employee
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= $e(
                                        ($attendance['employee_code'] ?? '') .
                                        ' - ' .
                                        $employeeName
                                    ) ?>"
                                    readonly
                                >

                                <input
                                    type="hidden"
                                    name="employee_id"
                                    value="<?= (int) $employeeId ?>"
                                >

                            </div>

                            <!-- Date -->
                            <div class="col-md-6">

                                <label
                                    for="attendance_date"
                                    class="form-label"
                                >
                                    Attendance Date
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="date"
                                    name="attendance_date"
                                    id="attendance_date"
                                    class="form-control"
                                    value="<?= $e($date) ?>"
                                    required
                                >

                            </div>

                            <!-- Time In -->
                            <div class="col-md-6">

                                <label
                                    for="time_in"
                                    class="form-label"
                                >
                                    Time In
                                </label>

                                <input
                                    type="time"
                                    name="time_in"
                                    id="time_in"
                                    class="form-control"
                                    value="<?= $e($timeIn) ?>"
                                >

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-success mt-2"
                                    id="clockInNow"
                                >
                                    <i data-lucide="play"></i>
                                    Clock in now
                                </button>

                            </div>

                            <!-- Time Out -->
                            <div class="col-md-6">

                                <label
                                    for="time_out"
                                    class="form-label"
                                >
                                    Time Out
                                </label>

                                <input
                                    type="time"
                                    name="time_out"
                                    id="time_out"
                                    class="form-control"
                                    value="<?= $e($timeOut) ?>"
                                >

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger mt-2"
                                    id="clockOutNow"
                                >
                                    <i data-lucide="square"></i>
                                    Clock out now
                                </button>

                                <div
                                    class="form-text"
                                    id="workedTimePreview"
                                ></div>

                            </div>

                            <!-- Break -->
                            <div class="col-md-6">

                                <label
                                    for="break_minutes"
                                    class="form-label"
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
                                    value="<?= $e($breakMinutes) ?>"
                                >

                            </div>

                            <!-- Status -->
                            <div class="col-md-6">

                                <label
                                    for="status"
                                    class="form-label"
                                >
                                    Status
                                </label>

                                <select
                                    name="status"
                                    id="status"
                                    class="form-select"
                                >

                                    <?php
                                    $statuses = [
                                        'present' => 'Present',
                                        'late' => 'Late',
                                        'absent' => 'Absent',
                                        'half_day' => 'Half Day',
                                        'leave' => 'Leave',
                                        'rest_day' => 'Rest Day',
                                        'holiday' => 'Holiday'
                                    ];
                                    ?>

                                    <?php foreach ($statuses as $value => $label): ?>

                                        <option
                                            value="<?= $e($value) ?>"
                                            <?= $status === $value ? 'selected' : '' ?>
                                        >
                                            <?= $e($label) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <!-- Notes -->
                            <div class="col-12">

                                <label
                                    for="notes"
                                    class="form-label"
                                >
                                    Notes
                                </label>

                                <textarea
                                    name="notes"
                                    id="notes"
                                    class="form-control"
                                    rows="4"
                                    maxlength="255"
                                ><?= $e($notes) ?></textarea>

                            </div>

                            <!-- Calculated values -->
                            <div class="col-12">

                                <div class="card bg-light border">

                                    <div class="card-body">

                                        <h6 class="mb-3">
                                            Current Calculation
                                        </h6>

                                        <div class="row g-3">

                                            <div class="col-md-4">

                                                <div class="text-muted small">
                                                    Regular Hours
                                                </div>

                                                <div class="fw-bold">
                                                    <?= number_format(
                                                        (float) ($attendance['regular_hours'] ?? 0),
                                                        2
                                                    ) ?>
                                                    h
                                                </div>

                                            </div>

                                            <div class="col-md-4">

                                                <div class="text-muted small">
                                                    Overtime
                                                </div>

                                                <div class="fw-bold">
                                                    <?= number_format(
                                                        (float) ($attendance['overtime_hours'] ?? 0),
                                                        2
                                                    ) ?>
                                                    h
                                                </div>

                                            </div>

                                            <div class="col-md-4">

                                                <div class="text-muted small">
                                                    Late
                                                </div>

                                                <div class="fw-bold">
                                                    <?= (int) ($attendance['late_minutes'] ?? 0) ?>
                                                    min
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- Buttons -->
                            <div class="col-12">

                                <hr>

                                <div class="d-flex justify-content-end gap-2">

                                    <a
                                        href="<?= BASE_URL ?>/attendance"
                                        class="btn btn-outline-secondary"
                                    >
                                        Cancel
                                    </a>

                                    <button
                                        type="submit"
                                        class="btn btn-primary d-inline-flex align-items-center gap-2"
                                    >
                                        <i data-lucide="save"></i>
                                        Update Attendance
                                    </button>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const status = document.getElementById('status');
    const timeIn = document.getElementById('time_in');
    const timeOut = document.getElementById('time_out');
    const breakMinutes = document.getElementById('break_minutes');
    const attendanceDate = document.getElementById('attendance_date');
    const clockInNow = document.getElementById('clockInNow');
    const clockOutNow = document.getElementById('clockOutNow');
    const workedTimePreview = document.getElementById('workedTimePreview');

    function currentTime() {
        const now = new Date();
        return String(now.getHours()).padStart(2, '0') + ':' +
            String(now.getMinutes()).padStart(2, '0');
    }

    function currentDate() {
        const now = new Date();
        return now.getFullYear() + '-' +
            String(now.getMonth() + 1).padStart(2, '0') + '-' +
            String(now.getDate()).padStart(2, '0');
    }

    function updateWorkedTime() {
        if (!workedTimePreview || !timeIn.value || !timeOut.value) {
            if (workedTimePreview) {
                workedTimePreview.textContent = '';
            }
            return;
        }

        const start = timeIn.value.split(':');
        const end = timeOut.value.split(':');
        const startMinutes = (Number(start[0]) * 60) + Number(start[1]);
        const endMinutes = (Number(end[0]) * 60) + Number(end[1]);
        const breakValue = Number(breakMinutes.value || 0);
        const workedMinutes = endMinutes - startMinutes - breakValue;

        if (workedMinutes <= 0) {
            workedTimePreview.textContent = 'Time out must be later than time in.';
            workedTimePreview.className = 'form-text text-danger';
            return;
        }

        workedTimePreview.textContent = 'Estimated worked time: ' +
            Math.floor(workedMinutes / 60) + 'h ' +
            (workedMinutes % 60) + 'm after break.';
        workedTimePreview.className = 'form-text text-success';
    }

    if (clockInNow) {
        clockInNow.addEventListener('click', function () {
            timeIn.value = currentTime();
            if (!attendanceDate.value) {
                attendanceDate.value = currentDate();
            }
            updateWorkedTime();
        });
    }

    if (clockOutNow) {
        clockOutNow.addEventListener('click', function () {
            timeOut.value = currentTime();
            if (!attendanceDate.value) {
                attendanceDate.value = currentDate();
            }
            updateWorkedTime();
        });
    }

    timeIn.addEventListener('input', updateWorkedTime);
    timeOut.addEventListener('input', updateWorkedTime);
    breakMinutes.addEventListener('input', updateWorkedTime);

    function updateFields() {

        const specialStatuses = [
            'absent',
            'leave',
            'rest_day',
            'holiday'
        ];

        if (specialStatuses.includes(status.value)) {

            timeIn.disabled = true;
            timeOut.disabled = true;
            clockInNow.disabled = true;
            clockOutNow.disabled = true;
            breakMinutes.disabled = true;

        } else {

            timeIn.disabled = false;
            timeOut.disabled = false;
            clockInNow.disabled = false;
            clockOutNow.disabled = false;
            breakMinutes.disabled = false;

        }
    }

    status.addEventListener('change', updateFields);

    updateFields();
    updateWorkedTime();

});
</script>