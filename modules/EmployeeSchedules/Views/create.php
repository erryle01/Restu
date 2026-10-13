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

?>

<div class="container-fluid py-4">

    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="d-flex align-items-center mb-4">

        <a
            href="<?= BASE_URL ?>/schedules"
            class="btn btn-outline-secondary me-3"
            title="Back to Schedules"
        >
            <i data-lucide="arrow-left"></i>
        </a>

        <div>

            <h1 class="h3 mb-1">

                <i data-lucide="calendar-plus"></i>

                Create Weekly Schedule

            </h1>

            <p class="text-muted mb-0">
                Set the weekly working schedule for an employee.
            </p>

        </div>

    </div>


    <!-- =====================================================
         ERROR
    ====================================================== -->

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


    <!-- =====================================================
         FORM
    ====================================================== -->

    <form
        method="POST"
        action="<?= BASE_URL ?>/schedules/create"
        id="scheduleForm"
    >

        <!-- =================================================
             EMPLOYEE
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <strong>

                    <i data-lucide="user"></i>

                    Employee

                </strong>

            </div>


            <div class="card-body">

                <div class="row">

                    <div class="col-lg-6">

                        <label
                            for="employee_id"
                            class="form-label"
                        >

                            Employee

                            <span class="text-danger">
                                *
                            </span>

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

                            <?php foreach (
                                $employees ?? []
                                as $employee
                            ): ?>

                                <?php

                                $middleName =
                                    trim(
                                        $employee['middle_name']
                                        ?? ''
                                    );

                                $fullName =
                                    trim(
                                        $employee['first_name']
                                        . ' '
                                        . (
                                            $middleName !== ''
                                                ? $middleName . ' '
                                                : ''
                                        )
                                        . $employee['last_name']
                                    );

                                ?>

                                <option
                                    value="<?= (int) $employee['id'] ?>"
                                >

                                    <?= $e(
                                        $employee['employee_code']
                                    ) ?>

                                    -
                                    <?= $e($fullName) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <div class="form-text">
                            Select the employee whose weekly schedule
                            you want to configure.
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             WEEKLY SCHEDULE
        ================================================== -->

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <div
                    class="d-flex justify-content-between
                           align-items-center flex-wrap gap-2"
                >

                    <strong>

                        <i data-lucide="calendar-days"></i>

                        Weekly Schedule

                    </strong>


                    <div class="d-flex gap-2">

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            id="setWeekdayButton"
                        >

                            <i data-lucide="briefcase"></i>

                            Set Mon–Fri

                        </button>


                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            id="clearAllButton"
                        >

                            <i data-lucide="eraser"></i>

                            Clear

                        </button>

                    </div>

                </div>

            </div>


            <div class="card-body p-0">

                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0"
                    >

                        <thead class="table-light">

                            <tr>

                                <th
                                    style="min-width: 130px;"
                                >
                                    Day
                                </th>

                                <th
                                    style="min-width: 150px;"
                                >
                                    Time In
                                </th>

                                <th
                                    style="min-width: 150px;"
                                >
                                    Time Out
                                </th>

                                <th
                                    style="min-width: 150px;"
                                >
                                    Break
                                </th>

                                <th
                                    class="text-center"
                                    style="min-width: 120px;"
                                >
                                    Rest Day
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $days as $dayNumber => $dayName
                            ): ?>

                                <tr
                                    data-day="<?= $dayNumber ?>"
                                >

                                    <!-- DAY -->
                                    <td>

                                        <strong>

                                            <?= $e(
                                                $dayName
                                            ) ?>

                                        </strong>

                                    </td>


                                    <!-- TIME IN -->
                                    <td>

                                        <input
                                            type="time"
                                            class="form-control time-in"
                                            name="time_in[<?= $dayNumber ?>]"
                                            value="08:00"
                                        >

                                    </td>


                                    <!-- TIME OUT -->
                                    <td>

                                        <input
                                            type="time"
                                            class="form-control time-out"
                                            name="time_out[<?= $dayNumber ?>]"
                                            value="17:00"
                                        >

                                    </td>


                                    <!-- BREAK -->
                                    <td>

                                        <div class="input-group">

                                            <input
                                                type="number"
                                                class="form-control break-minutes"
                                                name="break_minutes[<?= $dayNumber ?>]"
                                                value="60"
                                                min="0"
                                                max="1440"
                                                step="1"
                                            >

                                            <span
                                                class="input-group-text"
                                            >
                                                min
                                            </span>

                                        </div>

                                    </td>


                                    <!-- REST DAY -->
                                    <td class="text-center">

                                        <div
                                            class="form-check
                                                   d-flex
                                                   justify-content-center"
                                        >

                                            <input
                                                type="checkbox"
                                                class="form-check-input rest-day"
                                                name="rest_day[<?= $dayNumber ?>]"
                                                value="1"
                                                id="rest_day_<?= $dayNumber ?>"
                                            >

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- =================================================
                 INFORMATION
            ================================================== -->

            <div class="card-footer bg-white">

                <div class="alert alert-info mb-0">

                    <div class="d-flex">

                        <i
                            data-lucide="info"
                            class="me-2 flex-shrink-0"
                        ></i>

                        <div>

                            <strong>
                                Schedule information
                            </strong>

                            <div class="small mt-1">

                                Working days require a time-in
                                and time-out.

                                Rest days automatically use
                                <strong>0 minutes</strong>
                                break time.

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             ACTIONS
        ================================================== -->

        <div
            class="d-flex justify-content-end gap-2 mt-4"
        >

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

                Save Weekly Schedule

            </button>

        </div>

    </form>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================== -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
         * Initialize Lucide.
         */
        if (
            typeof lucide !== 'undefined'
        ) {

            lucide.createIcons();

        }


        const rows =
            document.querySelectorAll(
                'tbody tr[data-day]'
            );

        const weekdayButton =
            document.getElementById(
                'setWeekdayButton'
            );

        const clearAllButton =
            document.getElementById(
                'clearAllButton'
            );

        const form =
            document.getElementById(
                'scheduleForm'
            );


        /*
         * Enable / disable time and break
         * inputs for rest days.
         */
        function updateRestDay(row) {

            const restCheckbox =
                row.querySelector(
                    '.rest-day'
                );

            const timeIn =
                row.querySelector(
                    '.time-in'
                );

            const timeOut =
                row.querySelector(
                    '.time-out'
                );

            const breakMinutes =
                row.querySelector(
                    '.break-minutes'
                );


            if (
                restCheckbox.checked
            ) {

                timeIn.disabled = true;
                timeOut.disabled = true;
                breakMinutes.disabled = true;

                timeIn.value = '00:00';
                timeOut.value = '00:00';
                breakMinutes.value = '0';

                row.classList.add(
                    'table-secondary'
                );

            } else {

                timeIn.disabled = false;
                timeOut.disabled = false;
                breakMinutes.disabled = false;

                /*
                 * Restore sensible defaults
                 * if the values are zero.
                 */
                if (
                    timeIn.value === '00:00'
                ) {
                    timeIn.value = '08:00';
                }

                if (
                    timeOut.value === '00:00'
                ) {
                    timeOut.value = '17:00';
                }

                if (
                    breakMinutes.value === '0'
                ) {
                    breakMinutes.value = '60';
                }

                row.classList.remove(
                    'table-secondary'
                );

            }

        }


        /*
         * Attach rest-day events.
         */
        rows.forEach(
            function (row) {

                const checkbox =
                    row.querySelector(
                        '.rest-day'
                    );

                if (checkbox) {

                    checkbox.addEventListener(
                        'change',
                        function () {

                            updateRestDay(
                                row
                            );

                        }
                    );

                    updateRestDay(row);

                }

            }
        );


        /*
         * Set Monday-Friday as working days.
         */
        if (weekdayButton) {

            weekdayButton.addEventListener(
                'click',
                function () {

                    rows.forEach(
                        function (row) {

                            const day =
                                parseInt(
                                    row.dataset.day,
                                    10
                                );

                            const checkbox =
                                row.querySelector(
                                    '.rest-day'
                                );

                            const timeIn =
                                row.querySelector(
                                    '.time-in'
                                );

                            const timeOut =
                                row.querySelector(
                                    '.time-out'
                                );

                            const breakMinutes =
                                row.querySelector(
                                    '.break-minutes'
                                );


                            if (
                                day >= 1
                                &&
                                day <= 5
                            ) {

                                checkbox.checked =
                                    false;

                                timeIn.value =
                                    '08:00';

                                timeOut.value =
                                    '17:00';

                                breakMinutes.value =
                                    '60';

                            }


                            updateRestDay(row);

                        }
                    );

                }
            );

        }


        /*
         * Clear all schedules.
         */
        if (clearAllButton) {

            clearAllButton.addEventListener(
                'click',
                function () {

                    rows.forEach(
                        function (row) {

                            const checkbox =
                                row.querySelector(
                                    '.rest-day'
                                );

                            const timeIn =
                                row.querySelector(
                                    '.time-in'
                                );

                            const timeOut =
                                row.querySelector(
                                    '.time-out'
                                );

                            const breakMinutes =
                                row.querySelector(
                                    '.break-minutes'
                                );


                            checkbox.checked =
                                false;

                            timeIn.value =
                                '';

                            timeOut.value =
                                '';

                            breakMinutes.value =
                                '60';


                            updateRestDay(row);

                        }
                    );

                }
            );

        }


        /*
         * Form validation.
         */
        if (form) {

            form.addEventListener(
                'submit',
                function (event) {

                    let valid = true;

                    let errorMessage = '';


                    rows.forEach(
                        function (row) {

                            const day =
                                row.dataset.day;

                            const checkbox =
                                row.querySelector(
                                    '.rest-day'
                                );

                            const timeIn =
                                row.querySelector(
                                    '.time-in'
                                );

                            const timeOut =
                                row.querySelector(
                                    '.time-out'
                                );


                            /*
                             * Rest days don't require
                             * working hours.
                             */
                            if (
                                checkbox.checked
                            ) {

                                return;

                            }


                            /*
                             * Working days need
                             * time in/out.
                             */
                            if (
                                !timeIn.value
                                ||
                                !timeOut.value
                            ) {

                                valid = false;


                                const dayNames = {
                                    1: 'Monday',
                                    2: 'Tuesday',
                                    3: 'Wednesday',
                                    4: 'Thursday',
                                    5: 'Friday',
                                    6: 'Saturday',
                                    7: 'Sunday'
                                };


                                errorMessage =
                                    dayNames[day]
                                    +
                                    ': Time in and time out are required.';

                            }

                        }
                    );


                    if (!valid) {

                        event.preventDefault();

                        alert(
                            errorMessage
                        );

                    }

                }
            );

        }

    }
);

</script>