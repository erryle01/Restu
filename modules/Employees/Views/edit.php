
<?php

$e = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$employee = $employee ?? [];

$employeeId = (int) ($employee['id'] ?? 0);

$currentDepartmentId =
    (int) ($employee['department_id'] ?? 0);

$currentPositionId =
    (int) ($employee['position_id'] ?? 0);

$currentStatus =
    $employee['employment_status'] ?? 'active';

$currentSalaryType =
    $employee['salary_type'] ?? 'monthly';

$currentSalary =
    $employee['basic_salary'] ?? '0.00';

$currentHireDate =
    $employee['hire_date'] ?? '';

?>

<div class="container-fluid py-4">

    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="d-flex align-items-center mb-4">

        <a
            href="<?= BASE_URL ?>/employees"
            class="btn btn-outline-secondary me-3"
            title="Back to Employees"
        >
            <i data-lucide="arrow-left"></i>
        </a>

        <div>

            <h1 class="h3 mb-1">

                <i data-lucide="user-pen"></i>

                Edit Employee

            </h1>

            <p class="text-muted mb-0">
                Update employee information and salary details.
            </p>

        </div>

    </div>


    <!-- =========================================================
         ERROR MESSAGE
    ========================================================== -->

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


    <!-- =========================================================
         EMPLOYEE FORM
    ========================================================== -->

    <form
        method="POST"
        action="<?= BASE_URL ?>/employees/edit/<?= $employeeId ?>"
        id="employeeForm"
    >

        <div class="row g-4">


            <!-- =================================================
                 PERSONAL INFORMATION
            ================================================== -->

            <div class="col-lg-8">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">

                        <strong>

                            <i data-lucide="user"></i>

                            Personal Information

                        </strong>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            <!-- Employee Code -->
                            <div class="col-md-4">

                                <label
                                    class="form-label"
                                    for="employee_code"
                                >
                                    Employee Code

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="employee_code"
                                    name="employee_code"
                                    maxlength="50"
                                    value="<?= $e(
                                        $employee['employee_code'] ?? ''
                                    ) ?>"
                                    required
                                >

                            </div>


                            <!-- First Name -->
                            <div class="col-md-4">

                                <label
                                    class="form-label"
                                    for="first_name"
                                >
                                    First Name

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="first_name"
                                    name="first_name"
                                    maxlength="100"
                                    value="<?= $e(
                                        $employee['first_name'] ?? ''
                                    ) ?>"
                                    required
                                >

                            </div>


                            <!-- Middle Name -->
                            <div class="col-md-4">

                                <label
                                    class="form-label"
                                    for="middle_name"
                                >
                                    Middle Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="middle_name"
                                    name="middle_name"
                                    maxlength="100"
                                    value="<?= $e(
                                        $employee['middle_name'] ?? ''
                                    ) ?>"
                                >

                            </div>


                            <!-- Last Name -->
                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="last_name"
                                >
                                    Last Name

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="last_name"
                                    name="last_name"
                                    maxlength="100"
                                    value="<?= $e(
                                        $employee['last_name'] ?? ''
                                    ) ?>"
                                    required
                                >

                            </div>


                            <!-- Hire Date -->
                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="hire_date"
                                >
                                    Hire Date

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    id="hire_date"
                                    name="hire_date"
                                    value="<?= $e(
                                        $currentHireDate
                                    ) ?>"
                                    required
                                >

                            </div>


                            <!-- Email -->
                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="email"
                                >
                                    Email
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    maxlength="150"
                                    value="<?= $e(
                                        $employee['email'] ?? ''
                                    ) ?>"
                                >

                            </div>


                            <!-- Phone -->
                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="phone"
                                >
                                    Phone
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    maxlength="50"
                                    value="<?= $e(
                                        $employee['phone'] ?? ''
                                    ) ?>"
                                >

                            </div>


                            <!-- Address -->
                            <div class="col-12">

                                <label
                                    class="form-label"
                                    for="address"
                                >
                                    Address
                                </label>

                                <textarea
                                    class="form-control"
                                    id="address"
                                    name="address"
                                    rows="3"
                                    maxlength="255"
                                ><?= $e(
                                    $employee['address'] ?? ''
                                ) ?></textarea>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 EMPLOYMENT + SALARY
            ================================================== -->

            <div class="col-lg-4">


                <!-- =================================================
                     EMPLOYMENT
                ================================================== -->

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white py-3">

                        <strong>

                            <i data-lucide="briefcase-business"></i>

                            Employment

                        </strong>

                    </div>


                    <div class="card-body">


                        <!-- Department -->
                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="department_id"
                            >
                                Department

                                <span class="text-danger">
                                    *
                                </span>

                            </label>

                            <select
                                class="form-select"
                                id="department_id"
                                name="department_id"
                                required
                            >

                                <option value="">
                                    Select department
                                </option>

                                <?php foreach (
                                    $departments ?? []
                                    as $department
                                ): ?>

                                    <?php
                                    $departmentId =
                                        (int) $department['id'];
                                    ?>

                                    <option
                                        value="<?= $departmentId ?>"
                                        <?= (
                                            $departmentId
                                            === $currentDepartmentId
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        <?= $e(
                                            $department['name']
                                        ) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Position -->
                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="position_id"
                            >
                                Position

                                <span class="text-danger">
                                    *
                                </span>

                            </label>

                            <select
                                class="form-select"
                                id="position_id"
                                name="position_id"
                                required
                            >

                                <option value="">
                                    Select position
                                </option>

                                <?php foreach (
                                    $positions ?? []
                                    as $position
                                ): ?>

                                    <?php
                                    $positionId =
                                        (int) $position['id'];

                                    $positionDepartmentId =
                                        (int) (
                                            $position[
                                                'department_id'
                                            ]
                                        );
                                    ?>

                                    <option
                                        value="<?= $positionId ?>"
                                        data-department="<?= $positionDepartmentId ?>"
                                        <?= (
                                            $positionId
                                            === $currentPositionId
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        <?= $e(
                                            $position['name']
                                        ) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <div
                                class="form-text"
                                id="positionHelp"
                            >
                                Only positions from the selected
                                department are available.
                            </div>

                        </div>


                        <!-- Employment Status -->
                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="employment_status"
                            >
                                Employment Status
                            </label>

                            <select
                                class="form-select"
                                id="employment_status"
                                name="employment_status"
                            >

                                <option
                                    value="active"
                                    <?= (
                                        $currentStatus
                                        === 'active'
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="inactive"
                                    <?= (
                                        $currentStatus
                                        === 'inactive'
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    Inactive
                                </option>

                                <option
                                    value="on_leave"
                                    <?= (
                                        $currentStatus
                                        === 'on_leave'
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    On Leave
                                </option>

                                <option
                                    value="terminated"
                                    <?= (
                                        $currentStatus
                                        === 'terminated'
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    Terminated
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     SALARY
                ================================================== -->

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">

                        <strong>

                            <i data-lucide="wallet"></i>

                            Salary

                        </strong>

                    </div>


                    <div class="card-body">


                        <!-- Salary Type -->
                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="salary_type"
                            >
                                Salary Type
                            </label>

                            <select
                                class="form-select"
                                id="salary_type"
                                name="salary_type"
                            >

                                <option
                                    value="monthly"
                                    <?= (
                                        $currentSalaryType
                                        === 'monthly'
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    Monthly
                                </option>

                                <option
                                    value="daily"
                                    <?= (
                                        $currentSalaryType
                                        === 'daily'
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    Daily
                                </option>

                                <option
                                    value="hourly"
                                    <?= (
                                        $currentSalaryType
                                        === 'hourly'
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    Hourly
                                </option>

                            </select>

                        </div>


                        <!-- Basic Salary -->
                        <div>

                            <label
                                class="form-label"
                                for="basic_salary"
                            >
                                Basic Salary
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    ₱
                                </span>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="basic_salary"
                                    name="basic_salary"
                                    min="0"
                                    step="0.01"
                                    value="<?= $e(
                                        number_format(
                                            (float) $currentSalary,
                                            2,
                                            '.',
                                            ''
                                        )
                                    ) ?>"
                                >

                            </div>

                            <div class="form-text">
                                Enter the employee's basic rate.
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================================================
             FORM ACTIONS
        ========================================================== -->

        <div
            class="d-flex justify-content-end gap-2 mt-4"
        >

            <a
                href="<?= BASE_URL ?>/employees"
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

                Update Employee

            </button>

        </div>

    </form>

</div>


<!-- =============================================================
     JAVASCRIPT
============================================================== -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
         * Initialize Lucide icons.
         */
        if (
            typeof lucide !== 'undefined'
        ) {

            lucide.createIcons();

        }


        const department =
            document.getElementById(
                'department_id'
            );

        const position =
            document.getElementById(
                'position_id'
            );

        const positionHelp =
            document.getElementById(
                'positionHelp'
            );

        const employeeForm =
            document.getElementById(
                'employeeForm'
            );


        /*
         * Make sure the elements exist.
         */
        if (
            !department ||
            !position
        ) {

            return;

        }


        /*
         * Filter positions according to
         * selected department.
         */
        function filterPositions(
            resetPosition = false
        ) {

            const departmentId =
                department.value;

            let selectedPositionIsValid =
                false;


            Array.from(
                position.options
            ).forEach(
                function (option) {

                    /*
                     * Always keep the placeholder.
                     */
                    if (!option.value) {

                        return;

                    }


                    const optionDepartment =
                        option.dataset.department;


                    const matches =
                        departmentId !== '' &&
                        optionDepartment
                        === departmentId;


                    option.hidden =
                        !matches;


                    if (
                        matches &&
                        option.value
                        === position.value
                    ) {

                        selectedPositionIsValid =
                            true;

                    }

                }
            );


            /*
             * When the department changes,
             * remove an invalid position.
             */
            if (
                resetPosition &&
                !selectedPositionIsValid
            ) {

                position.value = '';

            }


            /*
             * Disable position until
             * a department is selected.
             */
            if (
                departmentId === ''
            ) {

                position.disabled = true;

                if (positionHelp) {

                    positionHelp.textContent =
                        'Select a department first.';

                }

            } else {

                position.disabled = false;

                if (positionHelp) {

                    positionHelp.textContent =
                        'Only positions from the selected department are shown.';

                }

            }

        }


        /*
         * Initial filtering.
         *
         * Do NOT reset the current position
         * when loading the edit page.
         */
        filterPositions(false);


        /*
         * Filter positions when department
         * changes.
         */
        department.addEventListener(
            'change',
            function () {

                filterPositions(true);

            }
        );


        /*
         * Validate department + position
         * before submitting.
         */
        if (employeeForm) {

            employeeForm.addEventListener(
                'submit',
                function (event) {

                    const selectedOption =
                        position.options[
                            position.selectedIndex
                        ];


                    if (
                        !department.value ||
                        !position.value
                    ) {

                        return;

                    }


                    const selectedDepartment =
                        selectedOption.dataset.department;


                    if (
                        selectedDepartment
                        !== department.value
                    ) {

                        event.preventDefault();


                        alert(
                            'The selected position does not belong to the selected department.'
                        );


                        position.value = '';

                    }

                }
            );

        }

    }
);

</script>

