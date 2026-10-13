<div class="container-fluid py-4">

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                <i class="bi bi-speedometer2 me-1"></i>
                Dashboard
            </h2>

            <p class="text-muted mb-0">
                Welcome back,
                <strong>
                    <?= htmlspecialchars(
                        Auth::user()['full_name']
                        ?? Auth::user()['username']
                        ?? 'User',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>.
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


    <?php if (Auth::can('pos.view')): ?>
        <?php
        $weeklySales = $weeklySales ?? [];
        $weeklyPeak = max(array_map(
            static fn($day): float => (float) ($day['income'] ?? 0),
            $weeklySales ?: [['income' => 0]]
        ));
        ?>
        <style>
            .dashboard-sales-chart { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 12px; min-height: 190px; align-items: end; }
            .dashboard-sales-day { min-width: 0; text-align: center; }
            .dashboard-sales-track { height: 142px; display: flex; align-items: end; overflow: hidden; border-bottom: 1px solid #dce5e1; background: repeating-linear-gradient(to bottom, #fff 0, #fff 34px, #edf1ef 35px, #fff 36px); }
            .dashboard-sales-bar { width: min(34px, 70%); min-height: 0; margin: 0 auto; border-radius: 4px 4px 0 0; background: linear-gradient(180deg, #57b78d, #16815d); transition: height .25s ease; }
            .dashboard-sales-label { display: block; margin-top: 8px; color: #687873; font-size: 11px; }
            .dashboard-sales-value { display: block; overflow: hidden; margin-top: 2px; color: #26352f; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
            @media (max-width: 575.98px) { .dashboard-sales-chart { gap: 5px; } .dashboard-sales-track { height: 112px; } .dashboard-sales-value { font-size: 9px; } }
        </style>
        <section class="mb-4" aria-labelledby="salesOverviewTitle">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                <div>
                    <div class="text-muted small">SALES OVERVIEW</div>
                    <h3 class="h5 mb-0" id="salesOverviewTitle">Income and orders</h3>
                </div>
                <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/reports/sales">Sales report <i class="bi bi-arrow-up-right"></i></a>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">Today's income</div><div class="fs-3 fw-semibold"><?= number_format((float) ($todaySales['income'] ?? 0), 2) ?></div><div class="small text-muted"><?= (int) ($todaySales['order_count'] ?? 0) ?> paid orders</div></div>
                            <i class="bi bi-cash-stack fs-2 text-success"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">Month-to-date income</div><div class="fs-3 fw-semibold"><?= number_format((float) ($monthSales['income'] ?? 0), 2) ?></div><div class="small text-muted"><?= (int) ($monthSales['order_count'] ?? 0) ?> paid orders</div></div>
                            <i class="bi bi-graph-up-arrow fs-2 text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">Today's discounts</div><div class="fs-3 fw-semibold"><?= number_format((float) ($todaySales['discounts'] ?? 0), 2) ?></div><div class="small text-muted">Applied to paid orders</div></div>
                            <i class="bi bi-tag fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div><h4 class="h6 mb-0">Daily income</h4><div class="small text-muted">Paid orders · last 7 days</div></div>
                    <i class="bi bi-bar-chart-line text-success fs-5" aria-hidden="true"></i>
                </div>
                <div class="card-body">
                    <?php if (!$weeklySales): ?>
                        <div class="text-center text-muted py-4">No sales data available yet.</div>
                    <?php else: ?>
                        <div class="dashboard-sales-chart" role="img" aria-label="Daily paid income for the last seven days">
                            <?php foreach ($weeklySales as $day): ?>
                                <?php
                                $income = (float) ($day['income'] ?? 0);
                                $barHeight = $weeklyPeak > 0 ? ($income / $weeklyPeak) * 100 : 0;
                                $dayDate = strtotime($day['date']);
                                ?>
                                <div class="dashboard-sales-day" title="<?= htmlspecialchars(date('F j', $dayDate) . ': ' . number_format($income, 2) . ' income · ' . (int) ($day['order_count'] ?? 0) . ' orders', ENT_QUOTES, 'UTF-8') ?>">
                                    <div class="dashboard-sales-track"><div class="dashboard-sales-bar" style="height: <?= number_format($barHeight, 2, '.', '') ?>%"></div></div>
                                    <span class="dashboard-sales-label"><?= htmlspecialchars(date('D', $dayDate), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="dashboard-sales-value"><?= number_format($income, 0) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============================= -->
    <!-- STATISTICS -->
    <!-- ============================= -->

    <div class="row g-4 mb-4">


        <!-- TOTAL USERS -->
        <?php if (Auth::can('users.view')): ?>

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div
                            class="d-flex justify-content-between align-items-center"
                        >

                            <div>

                                <div
                                    class="text-muted small mb-1"
                                >
                                    Total Users
                                </div>

                                <h3 class="mb-0">
                                    <?= (int) $totalUsers ?>
                                </h3>

                            </div>

                            <div
                                class="fs-1 text-primary"
                            >
                                <i class="bi bi-people"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>


        <!-- ACTIVE USERS -->
        <?php if (Auth::can('users.view')): ?>

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div
                            class="d-flex justify-content-between align-items-center"
                        >

                            <div>

                                <div
                                    class="text-muted small mb-1"
                                >
                                    Active Users
                                </div>

                                <h3 class="mb-0">
                                    <?= (int) $activeUsers ?>
                                </h3>

                            </div>

                            <div
                                class="fs-1 text-success"
                            >
                                <i class="bi bi-person-check"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>


        <!-- ROLES -->
        <?php if (Auth::can('roles.view')): ?>

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div
                            class="d-flex justify-content-between align-items-center"
                        >

                            <div>

                                <div
                                    class="text-muted small mb-1"
                                >
                                    Roles
                                </div>

                                <h3 class="mb-0">
                                    <?= (int) $totalRoles ?>
                                </h3>

                            </div>

                            <div
                                class="fs-1 text-warning"
                            >
                                <i class="bi bi-shield-check"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>


        <!-- PERMISSIONS -->
        <?php if (Auth::can('roles.view')): ?>

            <div class="col-xl-3 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div
                            class="d-flex justify-content-between align-items-center"
                        >

                            <div>

                                <div
                                    class="text-muted small mb-1"
                                >
                                    Permissions
                                </div>

                                <h3 class="mb-0">
                                    <?= (int) $totalPermissions ?>
                                </h3>

                            </div>

                            <div
                                class="fs-1 text-info"
                            >
                                <i class="bi bi-key"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>


    <!-- ============================= -->
    <!-- SECONDARY STATISTICS -->
    <!-- ============================= -->

    <div class="row g-4 mb-4">


        <!-- EMPLOYEES -->
        <?php if (Auth::can('employees.view')): ?>

            <div class="col-lg-3 col-md-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <div class="text-muted small">
                            Employees
                        </div>

                        <h4 class="mt-2 mb-0">
                            <?= (int) $totalEmployees ?>
                        </h4>

                    </div>

                </div>

            </div>

        <?php endif; ?>


        <!-- INACTIVE -->
        <?php if (Auth::can('users.view')): ?>

            <div class="col-lg-3 col-md-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <div class="text-muted small">
                            Inactive Users
                        </div>

                        <h4 class="mt-2 mb-0">
                            <?= (int) $inactiveUsers ?>
                        </h4>

                    </div>

                </div>

            </div>


            <!-- LOCKED -->
            <div class="col-lg-3 col-md-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <div class="text-muted small">
                            Locked Users
                        </div>

                        <h4 class="mt-2 mb-0">
                            <?= (int) $lockedUsers ?>
                        </h4>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>


    <!-- ============================= -->
    <!-- RECENT USERS + ACTIVITY -->
    <!-- ============================= -->

    <div class="row g-4">


        <!-- RECENT USERS -->
        <?php if (Auth::can('users.view')): ?>

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">

                        <div
                            class="d-flex justify-content-between align-items-center"
                        >

                            <strong>
                                <i class="bi bi-people me-1"></i>
                                Recent Users
                            </strong>

                            <a
                                href="<?= BASE_URL ?>/users"
                                class="btn btn-sm btn-outline-primary"
                            >
                                View All
                            </a>

                        </div>

                    </div>


                    <div class="card-body p-0">

                        <?php if (empty($recentUsers)): ?>

                            <div
                                class="text-center text-muted py-4"
                            >
                                No users found.
                            </div>

                        <?php else: ?>

                            <div class="table-responsive">

                                <table
                                    class="table table-hover mb-0"
                                >

                                    <thead class="table-light">

                                        <tr>

                                            <th>
                                                User
                                            </th>

                                            <th>
                                                Status
                                            </th>

                                            <th>
                                                Created
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        <?php foreach (
                                            $recentUsers
                                            as $user
                                        ): ?>

                                            <tr>

                                                <td>

                                                    <strong>
                                                        <?= htmlspecialchars(
                                                            $user['full_name'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </strong>

                                                    <small
                                                        class="text-muted d-block"
                                                    >
                                                        @<?= htmlspecialchars(
                                                            $user['username'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </small>

                                                </td>


                                                <td>

                                                    <?php
                                                    $status =
                                                        $user['status'];
                                                    ?>

                                                    <?php if (
                                                        $status === 'active'
                                                    ): ?>

                                                        <span
                                                            class="badge bg-success"
                                                        >
                                                            Active
                                                        </span>

                                                    <?php elseif (
                                                        $status === 'locked'
                                                    ): ?>

                                                        <span
                                                            class="badge bg-danger"
                                                        >
                                                            Locked
                                                        </span>

                                                    <?php else: ?>

                                                        <span
                                                            class="badge bg-secondary"
                                                        >
                                                            Inactive
                                                        </span>

                                                    <?php endif; ?>

                                                </td>


                                                <td>

                                                    <small>
                                                        <?= htmlspecialchars(
                                                            $user['created_at'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </small>

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

        <?php endif; ?>


        <!-- RECENT ACTIVITY -->
        <?php if (
            Auth::can('dashboard.view')
        ): ?>

            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">

                        <strong>
                            <i class="bi bi-clock-history me-1"></i>
                            Recent Activity
                        </strong>

                    </div>


                    <div class="card-body p-0">

                        <?php if (
                            empty($recentActivities)
                        ): ?>

                            <div
                                class="text-center text-muted py-4"
                            >
                                No recent activity.
                            </div>

                        <?php else: ?>

                            <div class="list-group list-group-flush">

                                <?php foreach (
                                    $recentActivities
                                    as $activity
                                ): ?>

                                    <div
                                        class="list-group-item"
                                    >

                                        <div
                                            class="d-flex justify-content-between"
                                        >

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $activity['action']
                                                    ?? 'Activity',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </strong>

                                            <small
                                                class="text-muted"
                                            >

                                                <?= htmlspecialchars(
                                                    $activity['created_at']
                                                    ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </small>

                                        </div>


                                        <?php if (
                                            !empty(
                                                $activity['description']
                                            )
                                        ): ?>

                                            <small
                                                class="text-muted"
                                            >

                                                <?= htmlspecialchars(
                                                    $activity['description'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </small>

                                        <?php endif; ?>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        <?php endif; ?>


    </div>

</div>