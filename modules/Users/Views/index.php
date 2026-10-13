<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Users
            </h2>

            <p class="text-muted mb-0">
                Manage system accounts and access.
            </p>

        </div>

        <?php if (Auth::can('users.create')): ?>

            <a
                href="<?= BASE_URL ?>/users/create"
                class="btn btn-primary"
            >
                + Add User
            </a>

        <?php endif; ?>

    </div>


    <?php if (!empty($success)): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <div class="card shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>

                            <th>User</th>

                            <th>Full Name</th>

                            <th>Email</th>

                            <th>Roles</th>

                            <th>Status</th>

                            <th>Last Login</th>

                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (empty($users)): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-4 text-muted"
                            >
                                No users found.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($users as $user): ?>

                            <tr>

                                <td>
                                    <?= (int)$user['id'] ?>
                                </td>

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $user['username']
                                        ) ?>
                                    </strong>

                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $user['full_name']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $user['email'] ?? ''
                                    ) ?>
                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $user['roles'] ?? 'No role'
                                    ) ?>

                                </td>

                                <td>

                                    <?php if (
                                        $user['status'] === 'active'
                                    ): ?>

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    <?php elseif (
                                        $user['status'] === 'locked'
                                    ): ?>

                                        <span class="badge bg-danger">
                                            Locked
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?= $user['last_login']
                                        ? htmlspecialchars(
                                            $user['last_login']
                                        )
                                        : 'Never'
                                    ?>

                                </td>

                                <td class="text-end">

                                    <?php if (
                                        Auth::can('users.edit')
                                    ): ?>

                                        <a
                                            href="<?= BASE_URL ?>/users/edit/<?= (int)$user['id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Edit
                                        </a>

                                    <?php endif; ?>


                                    <?php if (
                                        Auth::can('users.edit') &&
                                        Auth::id() !== (int)$user['id']
                                    ): ?>

                                        <a
                                            href="<?= BASE_URL ?>/users/toggle/<?= (int)$user['id'] ?>"
                                            class="btn btn-sm btn-outline-warning"
                                            onclick="return confirm('Change this user status?')"
                                        >
                                            Toggle
                                        </a>

                                    <?php endif; ?>


                                    <?php if (
                                        Auth::can('users.delete') &&
                                        Auth::id() !== (int)$user['id']
                                    ): ?>

                                        <a
                                            href="<?= BASE_URL ?>/users/delete/<?= (int)$user['id'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this user permanently?')"
                                        >
                                            Delete
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