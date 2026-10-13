<?php
?>

<div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="mb-1">
                <i class="bi bi-shield-lock"></i>
                Roles & Permissions
            </h2>

            <p class="text-muted mb-0">
                Manage system roles and access permissions.
            </p>
        </div>


        <?php if (Auth::can('roles.create')): ?>

            <a
                href="<?= BASE_URL ?>/roles/create"
                class="btn btn-primary"
            >
                <i class="bi bi-plus-lg"></i>
                Create Role
            </a>

        <?php endif; ?>

    </div>


    <!-- Success Message -->
    <?php if (!empty($success)): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
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


    <!-- Error Message -->
    <?php if (!empty($error)): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-exclamation-triangle me-1"></i>

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


    <!-- Roles Card -->
    <div class="card shadow-sm border-0">

        <!-- Card Header -->
        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <strong>
                    <i class="bi bi-shield-check me-1"></i>
                    System Roles
                </strong>

                <span class="text-muted">
                    <?= count($roles) ?> role(s)
                </span>

            </div>

        </div>


        <!-- Card Body -->
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th width="70">
                                #
                            </th>

                            <th>
                                Role
                            </th>

                            <th>
                                Description
                            </th>

                            <th class="text-center">
                                Users
                            </th>

                            <th class="text-center">
                                Permissions
                            </th>

                            <th>
                                Created
                            </th>

                            <th width="160">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($roles)): ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5 text-muted"
                            >

                                <i
                                    class="bi bi-shield-x fs-1 d-block mb-2"
                                ></i>

                                <div>
                                    No roles found.
                                </div>

                                <?php if (Auth::can('roles.create')): ?>

                                    <a
                                        href="<?= BASE_URL ?>/roles/create"
                                        class="btn btn-sm btn-primary mt-3"
                                    >
                                        <i class="bi bi-plus-lg"></i>
                                        Create First Role
                                    </a>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php else: ?>


                        <?php foreach ($roles as $role): ?>

                            <?php
                                $isSuperAdmin =
                                    strcasecmp(
                                        $role['name'],
                                        'Super Admin'
                                    ) === 0;
                            ?>

                            <tr>

                                <!-- ID -->
                                <td>
                                    <?= (int) $role['id'] ?>
                                </td>


                                <!-- Role -->
                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $role['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </strong>


                                    <?php if ($isSuperAdmin): ?>

                                        <span
                                            class="badge bg-danger ms-1"
                                        >
                                            <i class="bi bi-lock-fill"></i>
                                            System
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Description -->
                                <td>

                                    <?php if (
                                        !empty($role['description'])
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $role['description'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No description
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Users -->
                                <td class="text-center">

                                    <span
                                        class="badge bg-secondary"
                                    >
                                        <?= (int) $role['user_count'] ?>
                                    </span>

                                </td>


                                <!-- Permissions -->
                                <td class="text-center">

                                    <span
                                        class="badge bg-primary"
                                    >
                                        <?= (int) $role['permission_count'] ?>
                                    </span>

                                </td>


                                <!-- Created -->
                                <td>

                                    <?php
                                        $createdAt =
                                            $role['created_at'] ?? '';
                                    ?>

                                    <?= htmlspecialchars(
                                        $createdAt,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- Actions -->
<td>

    <div class="btn-group" role="group">

        <!-- Edit -->
        <?php if (Auth::can('roles.edit')): ?>

            <a
                href="<?= BASE_URL ?>/roles/edit/<?= (int) $role['id'] ?>"
                class="btn btn-sm btn-outline-primary"
                title="Edit Role"
            >
                <i class="bi bi-pencil me-1"></i>
                Edit
            </a>

        <?php endif; ?>


        <!-- Delete -->
        <?php if (
            Auth::can('roles.delete')
            &&
            !$isSuperAdmin
        ): ?>

            <?php if (
                (int) $role['user_count'] === 0
            ): ?>

                <a
                    href="<?= BASE_URL ?>/roles/delete/<?= (int) $role['id'] ?>"
                    class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('Are you sure you want to delete the role <?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?>? This action cannot be undone.');"
                    title="Delete Role"
                >
                    <i class="bi bi-trash me-1"></i>
                    Delete
                </a>

            <?php else: ?>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    disabled
                    title="Cannot delete a role assigned to users"
                >
                    <i class="bi bi-trash me-1"></i>
                    Delete
                </button>

            <?php endif; ?>

        <?php endif; ?>

    </div>

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