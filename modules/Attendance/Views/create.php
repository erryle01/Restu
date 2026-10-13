<?php
$e = static function ($value): string {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};

$employees = $employees ?? [];
$error = $error ?? null;

$oldEmployeeId = $_POST['employee_id'] ?? '';
$oldDate = $_POST['attendance_date'] ?? date('Y-m-d');
$oldTimeIn = $_POST['time_in'] ?? '';
$oldTimeOut = $_POST['time_out'] ?? '';
$oldBreak = $_POST['break_minutes'] ?? '60';
$oldStatus = $_POST['status'] ?? 'present';
$oldNotes = $_POST['notes'] ?? '';
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <div class="d-flex align-items-center gap-2">

                <i data-lucide="clipboard-plus"></i>

                <h1 class="h3 mb-0">
                    Record Attendance
                </h1>

            </div>

            <p class="text-muted mb-0 mt-1">
                Record employee time in and time out.
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
                        action="<?= BASE_URL ?>/attendance/create"
                        id="attendanceForm"
                    >

                        <div class="row g-3">

                            <!-- Employee -->
                            <div class="col-md-6">

                                <label
                                    for="employee_id"
                                    class="form-label"
                                >
                                    Employee
                                    <span class="text-danger">*</span>
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
                                        $fullName = trim(
                                            ($employee['first_name'] ?? '') . ' ' .
                                            ($employee['middle_name'] ?? '') . ' ' .
                                            ($employee['last_name'] ?? '')
                                        );
                                        ?>

                                        <option
                                            value="<?= (int) $employee['id'] ?>"
                                            <?= ((string) $oldEmployeeId === (string) $employee['id']) ? 'selected' : '' ?>
                                        >
                                            <?= $e($employee['employee_code']) ?>
                                            -
                                            <?= $e($fullName) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

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
                                    value="<?= $e($oldDate) ?>"
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
                                    value="<?= $e($oldTimeIn) ?>"
                                >

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-success mt-2"
                                    id="clockInNow"
                                >
                                    <i data-lucide="play"></i>
                                    Clock in now
                                </button>

                                <div class="form-text">
                                    Leave blank for absent, leave or rest day.
                                </div>

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
                                    value="<?= $e($oldTimeOut) ?>"
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
                                    value="<?= $e($oldBreak) ?>"
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

                                    <option value="present" <?= $oldStatus === 'present' ? 'selected' : '' ?>>
                                        Present
                                    </option>

                                    <option value="late" <?= $oldStatus === 'late' ? 'selected' : '' ?>>
                                        Late
                                    </option>

                                    <option value="absent" <?= $oldStatus === 'absent' ? 'selected' : '' ?>>
                                        Absent
                                    </option>

                                    <option value="half_day" <?= $oldStatus === 'half_day' ? 'selected' : '' ?>>
                                        Half Day
                                    </option>

                                    <option value="leave" <?= $oldStatus === 'leave' ? 'selected' : '' ?>>
                                        Leave
                                    </option>

                                    <option value="rest_day" <?= $oldStatus === 'rest_day' ? 'selected' : '' ?>>
                                        Rest Day
                                    </option>

                                    <option value="holiday" <?= $oldStatus === 'holiday' ? 'selected' : '' ?>>
                                        Holiday
                                    </option>

                                </select>

                                <div class="form-text">
                                    The system will calculate attendance based on the schedule and actual time.
                                </div>

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
                                    placeholder="Optional attendance notes..."
                                ><?= $e($oldNotes) ?></textarea>

                            </div>

                            <!-- Submit -->
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
                                        Save Attendance
                                    </button>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-xl-4 mt-4 mt-xl-0">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex align-items-center gap-2 mb-3">

                        <i data-lucide="circle-help"></i>

                        <h5 class="mb-0">
                            Attendance Calculation
                        </h5>

                    </div>

                    <ul class="small text-muted mb-0">

                        <li class="mb-2">
                            The employee's schedule is checked based on the selected date.
                        </li>

                        <li class="mb-2">
                            Late minutes are calculated from the scheduled time in.
                        </li>

                        <li class="mb-2">
                            Break minutes are deducted from worked time.
                        </li>

                        <li class="mb-2">
                            Overtime is calculated when worked time exceeds scheduled hours.
                        </li>

                        <li>
                            Regular hours are stored separately from overtime hours.
                        </li>

                    </ul>

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

        if (!status) {
            return;
        }

        const specialStatuses = [
            'absent',
            'leave',
            'rest_day',
            'holiday'
        ];

        if (specialStatuses.includes(status.value)) {

            timeIn.value = '';
            timeOut.value = '';

            timeIn.disabled = true;
            timeOut.disabled = true;
            clockInNow.disabled = true;
            clockOutNow.disabled = true;

            breakMinutes.value = '0';
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