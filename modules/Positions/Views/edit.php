<div class="container-fluid py-4">

    <div class="mb-4">

        <h2>
            <i data-lucide="pencil" class="me-2"></i>
            Edit Position
        </h2>

        <p class="text-muted">
            Update position information and salary.
        </p>

    </div>


    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">

            <i data-lucide="triangle-alert" class="me-1"></i>

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
                action="<?= BASE_URL ?>/positions/edit/<?= (int) $position['id'] ?>"
            >

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label
                            for="department_id"
                            class="form-label"
                        >
                            Department
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="department_id"
                            id="department_id"
                            class="form-select"
                            required
                        >

                            <?php foreach (
                                $departments
                                as $department
                            ): ?>

                                <option
                                    value="<?= (int) $department['id'] ?>"
                                    <?= (int) $department['id']
                                        === (int) $position['department_id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= htmlspecialchars(
                                        $department['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-md-6 mb-3">

                        <label
                            for="name"
                            class="form-label"
                        >
                            Position Name
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control"
                            maxlength="100"
                            value="<?= htmlspecialchars(
                                $position['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >

                    </div>

                </div>


                <div class="mb-3">

                    <label
                        for="description"
                        class="form-label"
                    >
                        Description
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        class="form-control"
                        rows="3"
                        maxlength="255"
                    ><?= htmlspecialchars(
                        $position['description'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></textarea>

                </div>


                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label
                            for="salary_type"
                            class="form-label"
                        >
                            Salary Type
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="salary_type"
                            id="salary_type"
                            class="form-select"
                        >

                            <option
                                value="monthly"
                                <?= $position['salary_type']
                                    === 'monthly'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Monthly
                            </option>

                            <option
                                value="daily"
                                <?= $position['salary_type']
                                    === 'daily'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Daily
                            </option>

                            <option
                                value="hourly"
                                <?= $position['salary_type']
                                    === 'hourly'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Hourly
                            </option>

                        </select>

                    </div>


                    <div class="col-md-6 mb-3">

                        <label
                            for="basic_salary"
                            class="form-label"
                        >
                            Basic Salary
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                ₱
                            </span>

                            <input
                                type="number"
                                name="basic_salary"
                                id="basic_salary"
                                class="form-control"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars(
                                    $position['basic_salary'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >

                        </div>

                    </div>

                </div>


                <div class="mb-4">

                    <label
                        for="status"
                        class="form-label"
                    >
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="form-select"
                    >

                        <option
                            value="active"
                            <?= $position['status']
                                === 'active'
                                ? 'selected'
                                : '' ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?= $position['status']
                                === 'inactive'
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

                        <i
                            data-lucide="save"
                            class="me-1"
                        ></i>

                        Update Position

                    </button>


                    <a
                        href="<?= BASE_URL ?>/positions"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>