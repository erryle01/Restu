<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="mb-1">
                Edit User
            </h2>

            <p class="text-muted mb-0">
                Update user account information.
            </p>
        </div>

        <a
            href="<?= BASE_URL ?>/users"
            class="btn btn-secondary"
        >
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

    </div>

    <?php if (Session::getFlash('error')): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars(
                Session::getFlash('error')
            ) ?>
        </div>

    <?php endif; ?>


    <div class="card shadow-sm">

        <div class="card-body">

            <form
                method="POST"
                action="<?= BASE_URL ?>/users/edit/<?= (int)$user['id'] ?>"
            >

                <div class="row">

                    <!-- Username -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $user['username']
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- Full Name -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="full_name"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $user['full_name']
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- Email -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $user['email'] ?? ''
                            ) ?>"
                        >

                    </div>


                    <!-- Status -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option
                                value="active"
                                <?= $user['status'] === 'active'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                <?= $user['status'] === 'inactive'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Inactive
                            </option>

                            <option
                                value="locked"
                                <?= $user['status'] === 'locked'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Locked
                            </option>

                        </select>

                    </div>


                    <!-- New Password -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            New Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                        >

                        <small class="text-muted">
                            Leave blank to keep the current password.
                        </small>

                    </div>


                    <!-- Confirm Password -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Confirm New Password
                        </label>

                        <input
    type="password"
    name="confirm_password"
    class="form-control"
    placeholder="Confirm new password"
>

                    </div>

                </div>


                <hr>


                <!-- Roles -->

                <h5 class="mb-3">
                    Assign Roles
                </h5>

                <div class="row">

                    <?php foreach ($roles as $role): ?>

    <?php
    $checked = in_array(
        (int) $role['id'],
        $selectedRoles,
        true
    );
    ?>

    <div class="col-md-4 col-lg-3 mb-3">

        <div class="card h-100">

            <div class="card-body">

                <div class="form-check">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="roles[]"
                        value="<?= (int) $role['id'] ?>"
                        id="role_<?= (int) $role['id'] ?>"
                        <?= $checked ? 'checked' : '' ?>
                    >

                    <label
                        class="form-check-label fw-semibold"
                        for="role_<?= (int) $role['id'] ?>"
                    >
                        <?= htmlspecialchars($role['name']) ?>
                    </label>

                </div>

                <?php if (!empty($role['description'])): ?>

                    <small class="text-muted d-block mt-2">
                        <?= htmlspecialchars($role['description']) ?>
                    </small>

                <?php endif; ?>

            </div>

        </div>

    </div>

<?php endforeach; ?>

                </div>


                <div class="mt-4">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-save"></i>
                        Update User
                    </button>

                    <a
                        href="<?= BASE_URL ?>/users"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>