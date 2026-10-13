<div class="container-fluid py-4">

    <div class="mb-4">

        <h2>
            <i class="bi bi-plus-circle me-2"></i>
            Add Department
        </h2>

        <p class="text-muted">
            Create a department for restaurant employees.
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
                action="<?= BASE_URL ?>/departments/create"
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
                        placeholder="Example: Kitchen"
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
                        placeholder="Describe this department..."
                    ></textarea>

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

                        <option value="active">
                            Active
                        </option>

                        <option value="inactive">
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

                        Save Department

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