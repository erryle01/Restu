<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                <i class="bi bi-gear me-1"></i>
                Settings
            </h2>

            <p class="text-muted mb-0">
                Manage your system configuration.
            </p>

        </div>

    </div>


    <!-- SUCCESS -->
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


    <form
        method="POST"
        action="<?= BASE_URL ?>/settings/save"
    >

        <div class="row g-4">


            <!-- =========================== -->
            <!-- COMPANY -->
            <!-- =========================== -->

            <div class="col-12">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">

                        <h5 class="mb-0">

                            <i class="bi bi-building me-2"></i>

                            Company Information

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            <!-- COMPANY NAME -->
                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="company_name"
                                >
                                    Company Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="company_name"
                                    name="settings[company_name]"
                                    value="<?= htmlspecialchars(
                                        $settings['company'][0]['setting_value']
                                        ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            </div>


                            <!-- COMPANY PHONE -->
                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="company_phone"
                                >
                                    Phone
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="company_phone"
                                    name="settings[company_phone]"
                                    value="<?= htmlspecialchars(
                                        $settings['company'][2]['setting_value']
                                        ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            </div>


                            <!-- ADDRESS -->
                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="company_address"
                                >
                                    Address
                                </label>

                                <textarea
                                    class="form-control"
                                    id="company_address"
                                    name="settings[company_address]"
                                    rows="3"
                                ><?= htmlspecialchars(
                                    $settings['company'][1]['setting_value']
                                    ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?></textarea>

                            </div>


                            <!-- EMAIL -->
                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="company_email"
                                >
                                    Email
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="company_email"
                                    name="settings[company_email]"
                                    value="<?= htmlspecialchars(
                                        $settings['company_email'][0]['setting_value']
                                        ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================== -->
            <!-- POS -->
            <!-- =========================== -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white">

                        <h5 class="mb-0">

                            <i class="bi bi-cash-register me-2"></i>

                            POS Settings

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="currency"
                            >
                                Currency
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="currency"
                                name="settings[currency]"
                                value="<?= htmlspecialchars(
                                    $settings['pos'][0]['setting_value']
                                    ?? 'PHP',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        </div>


                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="currency_symbol"
                            >
                                Currency Symbol
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="currency_symbol"
                                name="settings[currency_symbol]"
                                value="<?= htmlspecialchars(
                                    $settings['pos'][1]['setting_value']
                                    ?? '₱',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        </div>


                        <div class="form-check form-switch mb-3">

                            <?php
                            $taxEnabled = '0';

                            foreach (
                                $settings['pos'] ?? []
                                as $setting
                            ) {
                                if (
                                    $setting['setting_key']
                                    === 'tax_enabled'
                                ) {
                                    $taxEnabled =
                                        $setting['setting_value'];
                                }
                            }
                            ?>

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="tax_enabled"
                                name="settings[tax_enabled]"
                                value="1"
                                <?= $taxEnabled === '1'
                                    ? 'checked'
                                    : '' ?>
                            >

                            <label
                                class="form-check-label"
                                for="tax_enabled"
                            >
                                Enable Tax
                            </label>

                        </div>


                        <div>

                            <label
                                class="form-label"
                                for="tax_rate"
                            >
                                Tax Rate (%)
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="form-control"
                                id="tax_rate"
                                name="settings[tax_rate]"
                                value="<?= htmlspecialchars(
                                    $settings['pos'][3]['setting_value']
                                    ?? '12',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================== -->
            <!-- RECEIPT -->
            <!-- =========================== -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white">

                        <h5 class="mb-0">

                            <i class="bi bi-receipt me-2"></i>

                            Receipt Settings

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="mb-3">

                            <label
                                class="form-label"
                            >
                                Receipt Header
                            </label>

                            <textarea
                                class="form-control"
                                name="settings[receipt_header]"
                                rows="3"
                            ><?= htmlspecialchars(
                                $settings['receipt_header'][0]['setting_value']
                                ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></textarea>

                        </div>


                        <div>

                            <label
                                class="form-label"
                            >
                                Receipt Footer
                            </label>

                            <textarea
                                class="form-control"
                                name="settings[receipt_footer]"
                                rows="3"
                            ><?= htmlspecialchars(
                                $settings['receipt_footer'][0]['setting_value']
                                ?? 'Thank you!',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></textarea>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================== -->
            <!-- PAYROLL -->
            <!-- =========================== -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">

                        <h5 class="mb-0">

                            <i class="bi bi-wallet2 me-2"></i>

                            Payroll Settings

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="mb-3">

                            <label class="form-label">
                                Payroll Frequency
                            </label>

                            <select
                                class="form-select"
                                name="settings[payroll_frequency]"
                            >

                                <?php
                                $frequency =
                                    $settings['payroll_frequency'][0]['setting_value']
                                    ?? 'monthly';
                                ?>

                                <option
                                    value="weekly"
                                    <?= $frequency === 'weekly'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Weekly
                                </option>

                                <option
                                    value="biweekly"
                                    <?= $frequency === 'biweekly'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Bi-Weekly
                                </option>

                                <option
                                    value="semi-monthly"
                                    <?= $frequency === 'semi-monthly'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Semi-Monthly
                                </option>

                                <option
                                    value="monthly"
                                    <?= $frequency === 'monthly'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Monthly
                                </option>

                            </select>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Standard Work Hours
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="1"
                                max="24"
                                class="form-control"
                                name="settings[standard_work_hours]"
                                value="<?= htmlspecialchars(
                                    $settings['standard_work_hours'][0]['setting_value']
                                    ?? '8',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        </div>


                        <div>

                            <label class="form-label">
                                Overtime Rate
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="1"
                                class="form-control"
                                name="settings[overtime_rate]"
                                value="<?= htmlspecialchars(
                                    $settings['overtime_rate'][0]['setting_value']
                                    ?? '1.25',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <small class="text-muted">
                                Example: 1.25 = 125% of regular hourly rate.
                            </small>

                        </div>

                        <div class="mt-3">

                            <label class="form-label">
                                Monthly Workday Divisor
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="1"
                                max="31"
                                class="form-control"
                                name="settings[monthly_workdays_per_period]"
                                value="<?= htmlspecialchars(
                                    $settings['monthly_workdays_per_period'][0]['setting_value']
                                    ?? '26',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <small class="text-muted">
                                Used only to derive the hourly rate for monthly-paid overtime. Set this to your approved payroll policy.
                            </small>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================== -->
            <!-- ATTENDANCE -->
            <!-- =========================== -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">

                        <h5 class="mb-0">

                            <i class="bi bi-clock me-2"></i>

                            Attendance Settings

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="mb-3">

                            <label class="form-label">
                                Late Grace Period
                            </label>

                            <div class="input-group">

                                <input
                                    type="number"
                                    min="0"
                                    class="form-control"
                                    name="settings[late_grace_minutes]"
                                    value="<?= htmlspecialchars(
                                        $settings['late_grace_minutes'][0]['setting_value']
                                        ?? '15',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                                <span class="input-group-text">
                                    minutes
                                </span>

                            </div>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Work Start Time
                            </label>

                            <input
                                type="time"
                                class="form-control"
                                name="settings[work_start_time]"
                                value="<?= htmlspecialchars(
                                    $settings['work_start_time'][0]['setting_value']
                                    ?? '08:00',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        </div>


                        <div>

                            <label class="form-label">
                                Work End Time
                            </label>

                            <input
                                type="time"
                                class="form-control"
                                name="settings[work_end_time]"
                                value="<?= htmlspecialchars(
                                    $settings['work_end_time'][0]['setting_value']
                                    ?? '17:00',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================== -->
            <!-- INVENTORY -->
            <!-- =========================== -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">

                        <h5 class="mb-0">

                            <i class="bi bi-box-seam me-2"></i>

                            Inventory Settings

                        </h5>

                    </div>


                    <div class="card-body">

                        <label class="form-label">
                            Low Stock Threshold
                        </label>

                        <input
                            type="number"
                            min="0"
                            class="form-control"
                            name="settings[low_stock_threshold]"
                            value="<?= htmlspecialchars(
                                $settings['low_stock_threshold'][0]['setting_value']
                                ?? '10',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                        <small class="text-muted">
                            Products at or below this quantity
                            can be marked as low stock.
                        </small>

                    </div>

                </div>

            </div>


            <!-- =========================== -->
            <!-- NAVIGATION -->
            <!-- =========================== -->

            <div class="col-12">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Navigation visibility</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted small">Choose which links appear in the sidebar. User permissions are still required to access each page.</p>
                        <div class="row g-2">
                            <?php
                            $navigationOptions = [
                                'nav_dashboard' => 'Dashboard',
                                'nav_pos' => 'POS',
                                'nav_products' => 'Products',
                                'nav_inventory' => 'Inventory',
                                'nav_sales_report' => 'Sales report',
                                'nav_tables' => 'Tables',
                                'nav_employees' => 'Employees',
                                'nav_departments' => 'Departments',
                                'nav_positions' => 'Positions',
                                'nav_schedules' => 'Schedules',
                                'nav_attendance' => 'Attendance',
                                'nav_payroll' => 'Payroll',
                                'nav_users' => 'Users',
                                'nav_roles' => 'Roles & Permissions',
                                'nav_settings' => 'Settings'
                            ];
                            ?>
                            <?php foreach ($navigationOptions as $key => $label): ?>
                                <?php
                                $enabled = '1';
                                foreach ($settings['navigation'] ?? [] as $navigationSetting) {
                                    if (($navigationSetting['setting_key'] ?? '') === $key) {
                                        $enabled = $navigationSetting['setting_value'];
                                        break;
                                    }
                                }
                                ?>
                                <div class="col-sm-6 col-lg-4">
                                    <label class="form-check form-switch border rounded px-3 py-2 d-flex justify-content-between align-items-center">
                                        <span class="form-check-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                        <input class="form-check-input ms-3" type="checkbox" role="switch" name="settings[<?= $key ?>]" value="1" <?= $enabled !== '0' ? 'checked' : '' ?>>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

            </div>


            <!-- =========================== -->
            <!-- SECURITY -->
            <!-- =========================== -->

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">

                        <h5 class="mb-0">

                            <i class="bi bi-shield-lock me-2"></i>

                            Security Settings

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="mb-3">

                            <label class="form-label">
                                Session Timeout
                            </label>

                            <div class="input-group">

                                <input
                                    type="number"
                                    min="1"
                                    class="form-control"
                                    name="settings[session_timeout]"
                                    value="<?= htmlspecialchars(
                                        $settings['session_timeout'][0]['setting_value']
                                        ?? '30',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                                <span class="input-group-text">
                                    minutes
                                </span>

                            </div>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Maximum Login Attempts
                            </label>

                            <input
                                type="number"
                                min="1"
                                class="form-control"
                                name="settings[max_login_attempts]"
                                value="<?= htmlspecialchars(
                                    $settings['max_login_attempts'][0]['setting_value']
                                    ?? '5',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        </div>


                        <div>

                            <label class="form-label">
                                Minimum Password Length
                            </label>

                            <input
                                type="number"
                                min="8"
                                class="form-control"
                                name="settings[password_min_length]"
                                value="<?= htmlspecialchars(
                                    $settings['password_min_length'][0]['setting_value']
                                    ?? '8',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        </div>

                    </div>

                </div>

            </div>


        </div>


        <!-- SAVE -->
        <?php if (Auth::can('settings.edit')): ?>

            <div class="d-flex justify-content-end mt-4">

                <button
                    type="submit"
                    class="btn btn-primary btn-lg"
                >

                    <i class="bi bi-save me-1"></i>

                    Save Settings

                </button>

            </div>

        <?php endif; ?>


    </form>

</div>