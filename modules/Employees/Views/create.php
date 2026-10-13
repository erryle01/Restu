<?php

$e = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

?>

<div class="container-fluid py-4">

    <div class="d-flex align-items-center mb-4">

        <a
            href="<?= BASE_URL ?>/employees"
            class="btn btn-outline-secondary me-3"
        >
            <i data-lucide="arrow-left"></i>
        </a>

        <div>
            <h1 class="h3 mb-1">
                <i data-lucide="user-plus"></i>
                Add Employee
            </h1>

            <p class="text-muted mb-0">
                Create a new employee record.
            </p>
        </div>

    </div>

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">
            <i data-lucide="circle-alert"></i>
            <?= $e($error) ?>
        </div>

    <?php endif; ?>

    <form
        method="POST"
        action="<?= BASE_URL ?>/employees/create"
        id="employeeForm"
    >

        <div class="row g-4">

            <div class="col-lg-8">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">
                        <strong>
                            <i data-lucide="user"></i>
                            Personal Information
                        </strong>
                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-md-4">

                                <label
                                    class="form-label"
                                    for="employee_code"
                                >
                                    Employee Code
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="employee_code"
                                    name="employee_code"
                                    maxlength="50"
                                    required
                                >

                            </div>

                            <div class="col-md-4">

                                <label
                                    class="form-label"
                                    for="first_name"
                                >
                                    First Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="first_name"
                                    name="first_name"
                                    maxlength="100"
                                    required
                                >

                            </div>

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
                                >

                            </div>

                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="last_name"
                                >
                                    Last Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="last_name"
                                    name="last_name"
                                    maxlength="100"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="hire_date"
                                >
                                    Hire Date
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    id="hire_date"
                                    name="hire_date"
                                    value="<?= date('Y-m-d') ?>"
                                    required
                                >

                            </div>

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
                                >

                            </div>

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
                                >

                            </div>

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
                                ></textarea>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-lg-4">

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white">
                        <strong>
                            <i data-lucide="briefcase-business"></i>
                            Employment
                        </strong>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="department_id"
                            >
                                Department
                                <span class="text-danger">*</span>
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
                                    $departments as $department
                                ): ?>

                                    <option
                                        value="<?= (int) $department['id'] ?>"
                                    >
                                        <?= $e(
                                            $department['name']
                                        ) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="position_id"
                            >
                                Position
                                <span class="text-danger">*</span>
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
                                    $positions as $position
                                ): ?>

                                    <option
                                        value="<?= (int) $position['id'] ?>"
                                        data-department="<?= (int) $position['department_id'] ?>"
                                    >
                                        <?= $e(
                                            $position['name']
                                        ) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

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

                                <option value="active">
                                    Active
                                </option>

                                <option value="inactive">
                                    Inactive
                                </option>

                                <option value="on_leave">
                                    On Leave
                                </option>

                                <option value="terminated">
                                    Terminated
                                </option>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">
                        <strong>
                            <i data-lucide="wallet"></i>
                            Salary
                        </strong>
                    </div>

                    <div class="card-body">

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

                                <option value="monthly">
                                    Monthly
                                </option>

                                <option value="daily">
                                    Daily
                                </option>

                                <option value="hourly">
                                    Hourly
                                </option>

                            </select>

                        </div>

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
                                    value="0.00"
                                >

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">

            <a
                href="<?= BASE_URL ?>/employees"
                class="btn btn-light"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                <i data-lucide="save"></i>
                Save Employee
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const department =
        document.getElementById('department_id');

    const position =
        document.getElementById('position_id');

    function filterPositions() {

        const departmentId =
            department.value;

        Array.from(position.options).forEach(
            function (option) {

                if (!option.value) {
                    return;
                }

                const optionDepartment =
                    option.dataset.department;

                option.hidden =
                    departmentId !== optionDepartment;
            }
        );

        if (
            position.value &&
            position.selectedOptions[0].hidden
        ) {
            position.value = '';
        }
    }

    department.addEventListener(
        'change',
        filterPositions
    );

    filterPositions();

});
</script>