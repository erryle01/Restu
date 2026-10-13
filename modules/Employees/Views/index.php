<?php

$e = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$formatSalary = static function (
    $value
): string {
    return number_format(
        (float) $value,
        2
    );
};

?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">
                <i data-lucide="users"></i>
                Employees
            </h1>

            <p class="text-muted mb-0">
                Manage restaurant employees and payroll information.
            </p>
        </div>

        <?php if (Auth::can('employees.create')): ?>

            <a
                href="<?= BASE_URL ?>/employees/create"
                class="btn btn-primary"
            >
                <i data-lucide="user-plus"></i>
                Add Employee
            </a>

        <?php endif; ?>

    </div>

    <?php if (!empty($success)): ?>

        <div class="alert alert-success alert-dismissible fade show">
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

        <div class="alert alert-danger alert-dismissible fade show">
            <i data-lucide="circle-alert"></i>
            <?= $e($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Employee Code</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Hire Date</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th class="text-end">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($employees)): ?>

                        <tr>
                            <td
                                colspan="8"
                                class="text-center py-5 text-muted"
                            >
                                <i
                                    data-lucide="users"
                                    style="width:40px;height:40px;"
                                ></i>

                                <div class="mt-2">
                                    No employees found.
                                </div>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($employees as $employee): ?>

                            <?php
                            $fullName = trim(
                                $employee['first_name']
                                . ' '
                                . (
                                    $employee['middle_name']
                                    ? $employee['middle_name'] . ' '
                                    : ''
                                )
                                . $employee['last_name']
                            );

                            $statusClass = match (
                                $employee['employment_status']
                            ) {
                                'active' =>
                                    'bg-success',

                                'inactive' =>
                                    'bg-secondary',

                                'on_leave' =>
                                    'bg-warning text-dark',

                                'terminated' =>
                                    'bg-danger',

                                default =>
                                    'bg-secondary'
                            };
                            ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?= $e(
                                            $employee['employee_code']
                                        ) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= $e($fullName) ?>

                                    <?php if (
                                        !empty($employee['email'])
                                    ): ?>

                                        <div class="small text-muted">
                                            <?= $e(
                                                $employee['email']
                                            ) ?>
                                        </div>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= $e(
                                        $employee['department_name']
                                    ) ?>
                                </td>

                                <td>
                                    <?= $e(
                                        $employee['position_name']
                                    ) ?>
                                </td>

                                <td>
                                    <?= $e(
                                        date(
                                            'M d, Y',
                                            strtotime(
                                                $employee['hire_date']
                                            )
                                        )
                                    ) ?>
                                </td>

                                <td>
                                    <strong>
                                        ₱<?= $formatSalary(
                                            $employee['basic_salary']
                                        ) ?>
                                    </strong>

                                    <div class="small text-muted">
                                        <?= $e(
                                            ucfirst(
                                                $employee['salary_type']
                                            )
                                        ) ?>
                                    </div>
                                </td>

                                <td>
                                    <span
                                        class="badge <?= $statusClass ?>"
                                    >
                                        <?= $e(
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $employee[
                                                        'employment_status'
                                                    ]
                                                )
                                            )
                                        ) ?>
                                    </span>
                                </td>

                                <td class="text-end">

                                    <?php if (
                                        Auth::can('employees.edit')
                                    ): ?>

                                        <a
                                            href="<?= BASE_URL ?>/employees/edit/<?= (int) $employee['id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Edit"
                                        >
                                            <i data-lucide="pencil"></i>
                                        </a>

                                    <?php endif; ?>

                                    <?php if (
                                        Auth::can('employees.delete')
                                    ): ?>

                                        <a
                                            href="<?= BASE_URL ?>/employees/delete/<?= (int) $employee['id'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this employee?');"
                                            title="Delete"
                                        >
                                            <i data-lucide="trash-2"></i>
                                        </a>

                                    <?php endif; ?>

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