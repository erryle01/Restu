<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                <i data-lucide="briefcase-business" class="me-2"></i>
                Positions
            </h2>

            <p class="text-muted mb-0">
                Manage restaurant employee positions and salary rates.
            </p>

        </div>


        <?php if (Auth::can('positions.create')): ?>

            <a
                href="<?= BASE_URL ?>/positions/create"
                class="btn btn-primary"
            >
                <i data-lucide="plus" class="me-1"></i>
                Add Position
            </a>

        <?php endif; ?>

    </div>


    <?php if (!empty($success)): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i data-lucide="circle-check" class="me-1"></i>

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

        <div class="alert alert-danger alert-dismissible fade show">

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

            <?php if (empty($positions)): ?>

                <div class="text-center py-5">

                    <i
                        data-lucide="briefcase-business"
                        style="width:48px;height:48px"
                        class="text-muted"
                    ></i>

                    <h5 class="mt-3">
                        No positions found
                    </h5>

                    <p class="text-muted">
                        Create your first employee position.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>ID</th>

                                <th>Position</th>

                                <th>Department</th>

                                <th>Salary Type</th>

                                <th>Basic Salary</th>

                                <th>Employees</th>

                                <th>Status</th>

                                <th class="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $positions
                                as $position
                            ): ?>

                                <tr>

                                    <td>
                                        <?= (int)
                                            $position['id'] ?>
                                    </td>


                                    <td>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $position['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>

                                        <?php if (
                                            !empty(
                                                $position['description']
                                            )
                                        ): ?>

                                            <div>
                                                <small
                                                    class="text-muted"
                                                >
                                                    <?= htmlspecialchars(
                                                        $position[
                                                            'description'
                                                        ],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </small>
                                            </div>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <span
                                            class="badge bg-light text-dark border"
                                        >
                                            <?= htmlspecialchars(
                                                $position[
                                                    'department_name'
                                                ],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>

                                    </td>


                                    <td>

                                        <?= ucfirst(
                                            htmlspecialchars(
                                                $position[
                                                    'salary_type'
                                                ],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        ₱<?= number_format(
                                            (float) $position[
                                                'basic_salary'
                                            ],
                                            2
                                        ) ?>

                                    </td>


                                    <td>

                                        <span
                                            class="badge bg-secondary"
                                        >
                                            <?= (int)
                                                $position[
                                                    'employee_count'
                                                ] ?>
                                        </span>

                                    </td>


                                    <td>

                                        <?php if (
                                            $position['status']
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


                                    <td class="text-end">

                                        <?php if (
                                            Auth::can(
                                                'positions.edit'
                                            )
                                        ): ?>

                                            <a
                                                href="<?= BASE_URL ?>/positions/edit/<?= (int) $position['id'] ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit"
                                            >
                                                <i
                                                    data-lucide="pencil"
                                                ></i>
                                            </a>


                                            <a
                                                href="<?= BASE_URL ?>/positions/toggle/<?= (int) $position['id'] ?>"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Toggle status"
                                            >
                                                <i
                                                    data-lucide="refresh-cw"
                                                ></i>
                                            </a>

                                        <?php endif; ?>


                                        <?php if (
                                            Auth::can(
                                                'positions.delete'
                                            )
                                        ): ?>

                                            <?php if (
                                                (int) $position[
                                                    'employee_count'
                                                ] === 0
                                            ): ?>

                                                <a
                                                    href="<?= BASE_URL ?>/positions/delete/<?= (int) $position['id'] ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Delete this position?');"
                                                    title="Delete"
                                                >
                                                    <i
                                                        data-lucide="trash-2"
                                                    ></i>
                                                </a>

                                            <?php else: ?>

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    disabled
                                                    title="Employees are assigned to this position"
                                                >
                                                    <i
                                                        data-lucide="lock"
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