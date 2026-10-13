<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="mb-1">
                Create User
            </h2>

            <p class="text-muted mb-0">
                Create a new system user account.
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


    <!-- Error Message -->
    <?php
    $error = Session::getFlash('error');
    ?>

    <?php if ($error): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- Create User Card -->
    <div class="card shadow-sm">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                <i class="bi bi-person-plus"></i>
                User Information
            </h5>

        </div>


        <div class="card-body">

            <form
                method="POST"
                action="<?= BASE_URL ?>/users/create"
            >

                <div class="row">

                    <!-- Username -->
                    <div class="col-md-6 mb-3">

                        <label
                            for="username"
                            class="form-label"
                        >
                            Username
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            placeholder="Enter username"
                            value="<?= htmlspecialchars(
                                $_POST['username'] ?? ''
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- Full Name -->
                    <div class="col-md-6 mb-3">

                        <label
                            for="full_name"
                            class="form-label"
                        >
                            Full Name
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            class="form-control"
                            placeholder="Enter full name"
                            value="<?= htmlspecialchars(
                                $_POST['full_name'] ?? ''
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- Email -->
                    <div class="col-md-6 mb-3">

                        <label
                            for="email"
                            class="form-label"
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter email address"
                            value="<?= htmlspecialchars(
                                $_POST['email'] ?? ''
                            ) ?>"
                        >

                    </div>


                    <!-- Status -->
                    <div class="col-md-6 mb-3">

                        <label
                            for="status"
                            class="form-label"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-select"
                        >

                            <option
                                value="active"
                                <?= ($_POST['status'] ?? 'active') === 'active'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                <?= ($_POST['status'] ?? '') === 'inactive'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Inactive
                            </option>

                            <option
                                value="locked"
                                <?= ($_POST['status'] ?? '') === 'locked'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Locked
                            </option>

                        </select>

                    </div>


                    <!-- Password -->
                    <div class="col-md-6 mb-3">

                        <label
                            for="password"
                            class="form-label"
                        >
                            Password
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter password"
                            required
                        >

                    </div>


                    <!-- Confirm Password -->
                    <div class="col-md-6 mb-3">

                    <label
    for="confirm_password"
    class="form-label"
>
    Confirm Password
    <span class="text-danger">*</span>
</label>

<input
    type="password"
    id="confirm_password"
    name="confirm_password"
    class="form-control"
    placeholder="Confirm password"
    required
>

                    </div>

                </div>


                <hr class="my-4">


                <!-- Roles -->
                <div class="mb-3">

                    <h5 class="mb-3">
                        <i class="bi bi-shield-lock"></i>
                        Assign Roles
                    </h5>

                    <p class="text-muted">
                        Select one or more roles for this user.
                    </p>

                </div>


                <div class="row">

                    <?php if (!empty($roles)): ?>

                        <?php foreach ($roles as $role): ?>

                            <div class="col-md-4 col-lg-3 mb-3">

                                <div class="card h-100">

                                    <div class="card-body">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="roles[]"
                                                value="<?= (int)$role['id'] ?>"
                                                id="role_<?= (int)$role['id'] ?>"
                                                <?= in_array(
                                                    (int)$role['id'],
                                                    array_map(
                                                        'intval',
                                                        $_POST['roles'] ?? []
                                                    ),
                                                    true
                                                )
                                                    ? 'checked'
                                                    : '' ?>
                                            >

                                            <label
                                                class="form-check-label fw-semibold"
                                                for="role_<?= (int)$role['id'] ?>"
                                            >
                                                <?= htmlspecialchars(
                                                    $role['name']
                                                ) ?>
                                            </label>

                                        </div>

                                        <?php if (!empty($role['description'])): ?>

                                            <small class="text-muted d-block mt-2">
                                                <?= htmlspecialchars(
                                                    $role['description']
                                                ) ?>
                                            </small>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="col-12">

                            <div class="alert alert-warning">

                                <i class="bi bi-exclamation-triangle"></i>

                                No roles are available.

                                Please create a role first.

                            </div>

                        </div>

                    <?php endif; ?>

                </div>


                <hr class="my-4">


                <!-- Buttons -->
                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-person-plus"></i>
                        Create User
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