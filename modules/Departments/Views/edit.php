<div class="container-fluid py-4">

    <div class="mb-4">

        <h2>
            <i class="bi bi-pencil-square me-2"></i>
            Edit Department
        </h2>

        <p class="text-muted">
            Update department information.
        </p>

    </div>


    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle me-1"></i>

            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <form
                method="POST"
                action="<?= BASE_URL ?>/departments/edit/<?= (int) $department['id'] ?>"
            >

                <div class="mb-3">

                    <label
                        for="name"
                        class="form-label"
                    >
                        Department Name
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="name"
                        name="name"
                        maxlength="100"
                        value="<?= htmlspecialchars(
                            $department['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >

                </div>


                <div class="mb-3">

                    <label
                        for="description"
                        class="form-label"
                    >
                        Description
                    </label>

                    <textarea
                        class="form-control"
                        id="description"
                        name="description"
                        rows="4"
                        maxlength="255"
                    ><?= htmlspecialchars(
                        $department['description'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></textarea>

                </div>


                <div class="mb-4">

                    <label
                        for="status"
                        class="form-label"
                    >
                        Status
                    </label>

                    <select
                        class="form-select"
                        id="status"
                        name="status"
                    >

                        <option
                            value="active"
                            <?= $department['status'] === 'active'
                                ? 'selected'
                                : '' ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?= $department['status'] === 'inactive'
                                ? 'selected'
                                : '' ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>


                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-save me-1"></i>

                        Update Department

                    </button>


                    <a
                        href="<?= BASE_URL ?>/departments"
                        class="btn btn-secondary"
                    >

                        Cancel

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>