<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                <i class="bi bi-diagram-3 me-2"></i>
                Departments
            </h2>

            <p class="text-muted mb-0">
                Manage restaurant employee departments.
            </p>

        </div>


        <?php if (Auth::can('departments.create')): ?>

            <a
                href="<?= BASE_URL ?>/departments/create"
                class="btn btn-primary"
            >
<i data-lucide="plus" class="me-1"></i>
Add Department

            </a>

        <?php endif; ?>

    </div>


    <?php if (!empty($success)): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
        >

            <i class="bi bi-check-circle me-1"></i>

            <?= htmlspecialchars(
                $success,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

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
        >

<i data-lucide="triangle-alert" class="me-1"></i>
            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <div class="card border-0 shadow-sm">

        <div class="card-body p-0">

            <?php if (empty($departments)): ?>

                <div class="text-center py-5">

                    <i
                        class="bi bi-diagram-3 fs-1 text-muted"
                    ></i>

                    <h5 class="mt-3">
                        No departments found
                    </h5>

                    <p class="text-muted">
                        Create your first restaurant department.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0"
                    >

                        <thead class="table-light">

                            <tr>

                                <th>ID</th>

                                <th>Department</th>

                                <th>Description</th>

                                <th>Employees</th>

                                <th>Status</th>

                                <th>Created</th>

                                <th class="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $departments
                                as $department
                            ): ?>

                                <tr>

                                    <td>
                                        <?= (int)
                                            $department['id'] ?>
                                    </td>


                                    <td>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $department['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $department['description']
                                            ?? '—',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>


                                    <td>

                                        <span
                                            class="badge bg-secondary"
                                        >
                                            <?= (int)
                                                $department[
                                                    'employee_count'
                                                ] ?>
                                        </span>

                                    </td>


                                    <td>

                                        <?php if (
                                            $department['status']
                                            === 'active'
                                        ): ?>

                                            <span
                                                class="badge bg-success"
                                            >
                                                Active
                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="badge bg-secondary"
                                            >
                                                Inactive
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <small>
                                            <?= htmlspecialchars(
                                                $department['created_at'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </small>

                                    </td>


                                    <td class="text-end">

                                        <?php if (
                                            Auth::can(
                                                'departments.edit'
                                            )
                                        ): ?>

                                            <a
                                                href="<?= BASE_URL ?>/departments/edit/<?= (int) $department['id'] ?>"
                                                class="btn btn-sm btn-outline-primary"
                                            >

                                               <i data-lucide="pencil"></i>

                                            </a>


                                            <a
                                                href="<?= BASE_URL ?>/departments/toggle/<?= (int) $department['id'] ?>"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Toggle status"
                                            >
<i data-lucide="refresh-cw"></i>

                                            </a>

                                        <?php endif; ?>


                                        <?php if (
                                            Auth::can(
                                                'departments.delete'
                                            )
                                        ): ?>

                                            <?php if (
                                                (int) $department[
                                                    'employee_count'
                                                ] === 0
                                            ): ?>

                                                <a
                                                    href="<?= BASE_URL ?>/departments/delete/<?= (int) $department['id'] ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Delete this department?');"
                                                >

                                                   <i data-lucide="trash-2"></i>

                                                </a>

                                            <?php else: ?>

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    disabled
                                                    title="Employees are assigned to this department"
                                                >

                                                    <i
                                                        class="bi bi-lock"
                                                    ></i>

                                                </button>

                                            <?php endif; ?>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>