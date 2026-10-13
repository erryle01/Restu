
<div class="container-fluid py-4">

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                <i class="bi bi-shield-plus"></i>
                Create Role
            </h2>

            <p class="text-muted mb-0">
                Create a role and assign its permissions.
            </p>

        </div>


        <a
            href="<?= BASE_URL ?>/roles"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

    </div>


    <!-- ERROR -->
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


    <!-- FORM -->
    <form
        method="POST"
        action="<?= BASE_URL ?>/roles/create"
    >

        <div class="row">


            <!-- ============================= -->
            <!-- ROLE INFORMATION -->
            <!-- ============================= -->

            <div class="col-lg-5">

                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-header bg-white">

                        <strong>
                            <i class="bi bi-info-circle me-1"></i>
                            Role Information
                        </strong>

                    </div>


                    <div class="card-body">


                        <!-- ROLE NAME -->
                        <div class="mb-3">

                            <label
                                for="name"
                                class="form-label"
                            >

                                Role Name

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                maxlength="50"
                                placeholder="Example: Manager"
                                required
                            >

                            <div class="form-text">
                                Enter a unique role name.
                            </div>

                        </div>


                        <!-- DESCRIPTION -->
                        <div class="mb-3">

                            <label
                                for="description"
                                class="form-label"
                            >
                                Description
                            </label>


                            <textarea
                                id="description"
                                name="description"
                                class="form-control"
                                rows="5"
                                placeholder="Describe what this role is responsible for..."
                            ></textarea>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ============================= -->
            <!-- PERMISSIONS -->
            <!-- ============================= -->

            <div class="col-lg-7">

                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-header bg-white">

                        <div
                            class="d-flex justify-content-between align-items-center"
                        >

                            <strong>
                                <i class="bi bi-key me-1"></i>
                                Permissions
                            </strong>


                            <div>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    onclick="selectAllPermissions()"
                                >

                                    <i class="bi bi-check-all"></i>

                                    Select All

                                </button>


                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    onclick="clearAllPermissions()"
                                >

                                    <i class="bi bi-x-lg"></i>

                                    Clear All

                                </button>

                            </div>

                        </div>

                    </div>


                    <div class="card-body">


                        <?php if (empty($permissions)): ?>

                            <div class="alert alert-warning mb-0">

                                <i class="bi bi-exclamation-circle"></i>

                                No permissions are available.

                            </div>

                        <?php else: ?>


                            <div class="row">

                                <?php foreach (
                                    $permissions as $permission
                                ): ?>

                                    <?php
                                        $permissionId =
                                            (int) $permission['id'];
                                    ?>


                                    <div class="col-md-6 mb-3">

                                        <div class="form-check">


                                            <input
                                                class="form-check-input permission-checkbox"
                                                type="checkbox"
                                                name="permissions[]"
                                                value="<?= $permissionId ?>"
                                                id="permission_<?= $permissionId ?>"
                                            >


                                            <label
                                                class="form-check-label"
                                                for="permission_<?= $permissionId ?>"
                                            >

                                                <!-- IMPORTANT:
                                                     permission_key, NOT name -->
                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $permission['permission_key'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>

                                                </strong>


                                                <?php if (
                                                    !empty(
                                                        $permission['description']
                                                    )
                                                ): ?>

                                                    <small
                                                        class="text-muted d-block"
                                                    >

                                                        <?= htmlspecialchars(
                                                            $permission['description'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>

                                                    </small>

                                                <?php endif; ?>

                                            </label>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>


                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- ============================= -->
        <!-- FORM ACTIONS -->
        <!-- ============================= -->

        <div
            class="d-flex justify-content-end gap-2"
        >

            <a
                href="<?= BASE_URL ?>/roles"
                class="btn btn-secondary"
            >

                <i class="bi bi-x-lg"></i>

                Cancel

            </a>


            <button
                type="submit"
                class="btn btn-primary"
            >

                <i class="bi bi-shield-plus"></i>

                Create Role

            </button>

        </div>

    </form>

</div>


<script>

function selectAllPermissions()
{
    document
        .querySelectorAll('.permission-checkbox')
        .forEach(function (checkbox) {

            checkbox.checked = true;

        });
}


function clearAllPermissions()
{
    document
        .querySelectorAll('.permission-checkbox')
        .forEach(function (checkbox) {

            checkbox.checked = false;

        });
}

</script>

