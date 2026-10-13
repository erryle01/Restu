
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars(APP_NAME) ?>
    </title>

    <link
        href="<?= BASE_URL ?>/assets/vendor/bootstrap/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="<?= BASE_URL ?>/assets/css/main.css"
        rel="stylesheet"/>

</head>


<body>


<?php

require_once BASE_PATH . '/modules/Settings/Models/SettingsModel.php';

$navigationSettings = new SettingsModel();
$showNav = static function (string $key) use ($navigationSettings): bool {
    return $navigationSettings->get('nav_' . $key, '1') !== '0';
};

/*
|--------------------------------------------------------------------------
| Current URL
|--------------------------------------------------------------------------
*/

$currentPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

$currentPath = rtrim($currentPath, '/');


/*
|--------------------------------------------------------------------------
| Active helper
|--------------------------------------------------------------------------
*/

$isActive = function (
    string $path,
    bool $exact = false
) use ($currentPath): bool {

    $target = rtrim(
        BASE_URL . $path,
        '/'
    );

    if ($exact) {
        return $currentPath === $target;
    }

    return $currentPath === $target
        || str_starts_with(
            $currentPath,
            $target . '/'
        );
};


/*
|--------------------------------------------------------------------------
| Permission helper
|--------------------------------------------------------------------------
*/

$can = function (string $permission): bool {

    return Auth::check()
        && Auth::can($permission);
};


/*
|--------------------------------------------------------------------------
| Sidebar group active helpers
|--------------------------------------------------------------------------
*/

$employeeSectionActive =
       $isActive('/employees')
    || $isActive('/departments')
    || $isActive('/positions')
    || $isActive('/schedules');

$attendanceSectionActive =
    $isActive('/attendance');

$systemSectionActive =
       $isActive('/users')
    || $isActive('/roles')
    || $isActive('/settings');

?>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<?php if (Auth::check()): ?>

<aside
    id="appSidebar"
    class="app-sidebar"
>

    <!-- BRAND -->

    <div class="sidebar-brand">

        <a
            href="<?= BASE_URL ?>/dashboard"
        >

            <i data-lucide="store"></i>

            <span>
                <?= htmlspecialchars(APP_NAME) ?>
            </span>

        </a>

    </div>


    <!-- NAVIGATION -->

    <nav class="sidebar-nav">


        <!-- =========================
             MAIN
        ========================== -->

        <div class="sidebar-section">
            Main
        </div>


        <?php if ($showNav('dashboard') && $can('dashboard.view')): ?>

            <a
                href="<?= BASE_URL ?>/dashboard"
                class="sidebar-link <?= $isActive('/dashboard', true) ? 'active' : '' ?>"
            >

                <i data-lucide="layout-dashboard"></i>

                <span>
                    Dashboard
                </span>

            </a>

        <?php endif; ?>


        <!-- =========================
             POS
        ========================== -->

        <?php if (
            ($showNav('pos') && ($can('pos.view') || $can('pos.create')))
            || ($showNav('products') && $can('products.view'))
            || ($showNav('inventory') && $can('inventory.view'))
            || ($showNav('sales_report') && $can('pos.view'))
            || ($showNav('tables') && $can('pos.edit'))
        ): ?>

            <div class="sidebar-section">
                Point of Sale
            </div>


            <?php if ($showNav('pos') && ($can('pos.view') || $can('pos.create'))): ?>

                <a
                    href="<?= BASE_URL ?>/pos"
                    class="sidebar-link <?= $isActive('/pos') ? 'active' : '' ?>"
                >

                    <i data-lucide="shopping-cart"></i>

                    <span>
                        POS
                    </span>

                </a>

            <?php endif; ?>


            <?php if ($showNav('products') && $can('products.view')): ?>

                <a
                    href="<?= BASE_URL ?>/products"
                    class="sidebar-link <?= $isActive('/products') ? 'active' : '' ?>"
                >

                    <i data-lucide="utensils"></i>

                    <span>
                        Products
                    </span>

                </a>

            <?php endif; ?>


            <?php if ($showNav('inventory') && $can('inventory.view')): ?>

                <a
                    href="<?= BASE_URL ?>/inventory"
                    class="sidebar-link <?= $isActive('/inventory') ? 'active' : '' ?>"
                >

                    <i data-lucide="boxes"></i>

                    <span>
                        Inventory
                    </span>

                </a>

            <?php endif; ?>

            <?php if ($showNav('sales_report') && $can('pos.view')): ?>

                <a
                    href="<?= BASE_URL ?>/reports/sales"
                    class="sidebar-link <?= $isActive('/reports') ? 'active' : '' ?>"
                >

                    <i data-lucide="chart-no-axes-combined"></i>

                    <span>
                        Sales report
                    </span>

                </a>

            <?php endif; ?>

            <?php if ($showNav('tables') && $can('pos.edit')): ?>

                <a
                    href="<?= BASE_URL ?>/tables"
                    class="sidebar-link <?= $isActive('/tables') ? 'active' : '' ?>"
                >

                    <i data-lucide="layout-grid"></i>

                    <span>
                        Tables
                    </span>

                </a>

            <?php endif; ?>

        <?php endif; ?>


        <!-- =========================
             HR / EMPLOYEES
        ========================== -->

        <?php if (
            ($showNav('employees') && $can('employees.view'))
            || ($showNav('departments') && $can('departments.view'))
            || ($showNav('positions') && $can('positions.view'))
            || ($showNav('schedules') && $can('schedules.view'))
            || ($showNav('attendance') && $can('attendance.view'))
            || ($showNav('payroll') && $can('payroll.view'))
        ): ?>

            <div class="sidebar-section">
                Human Resources
            </div>


            <!-- EMPLOYEES -->

            <?php if ($showNav('employees') && $can('employees.view')): ?>

                <a
                    href="<?= BASE_URL ?>/employees"
                    class="sidebar-link <?= $isActive('/employees') ? 'active' : '' ?>"
                >

                    <i data-lucide="users"></i>

                    <span>
                        Employees
                    </span>

                </a>

            <?php endif; ?>


            <!-- EMPLOYEE SETUP -->

            <?php if (
                ($showNav('departments') && $can('departments.view'))
                || ($showNav('positions') && $can('positions.view'))
                || ($showNav('schedules') && $can('schedules.view'))
            ): ?>

                <button
                    type="button"
                    class="sidebar-dropdown-toggle"
                    data-bs-toggle="collapse"
                    data-bs-target="#employeeSetup"
                    aria-expanded="<?= $employeeSectionActive ? 'true' : 'false' ?>"
                >

                    <i data-lucide="briefcase-business"></i>

                    <span>
                        Employee Setup
                    </span>

                    <i
                        data-lucide="chevron-down"
                        class="chevron"
                    ></i>

                </button>


                <div
                    id="employeeSetup"
                    class="collapse <?= $employeeSectionActive ? 'show' : '' ?>"
                >

                    <div class="sidebar-submenu">


                        <?php if ($showNav('departments') && $can('departments.view')): ?>

                            <a
                                href="<?= BASE_URL ?>/departments"
                                class="sidebar-link <?= $isActive('/departments') ? 'active' : '' ?>"
                            >

                                <span>
                                    Departments
                                </span>

                            </a>

                        <?php endif; ?>


                        <?php if ($showNav('positions') && $can('positions.view')): ?>

                            <a
                                href="<?= BASE_URL ?>/positions"
                                class="sidebar-link <?= $isActive('/positions') ? 'active' : '' ?>"
                            >

                                <span>
                                    Positions
                                </span>

                            </a>

                        <?php endif; ?>


                        <?php if ($showNav('schedules') && $can('schedules.view')): ?>

                            <a
                                href="<?= BASE_URL ?>/schedules"
                                class="sidebar-link <?= $isActive('/schedules') ? 'active' : '' ?>"
                            >

                                <span>
                                    Schedules
                                </span>

                            </a>

                        <?php endif; ?>


                    </div>

                </div>

            <?php endif; ?>


            <!-- ATTENDANCE -->

            <?php if ($showNav('attendance') && $can('attendance.view')): ?>

                <a
                    href="<?= BASE_URL ?>/attendance"
                    class="sidebar-link <?= $isActive('/attendance') ? 'active' : '' ?>"
                >

                    <i data-lucide="clock-3"></i>

                    <span>
                        Attendance
                    </span>

                </a>

            <?php endif; ?>


            <!-- PAYROLL -->

            <?php if ($showNav('payroll') && $can('payroll.view')): ?>

                <a
                    href="<?= BASE_URL ?>/payroll"
                    class="sidebar-link <?= $isActive('/payroll') ? 'active' : '' ?>"
                >

                    <i data-lucide="wallet-cards"></i>

                    <span>
                        Payroll
                    </span>

                </a>

            <?php endif; ?>


        <?php endif; ?>


        <!-- =========================
             SYSTEM
        ========================== -->

        <?php if (
            ($showNav('users') && $can('users.view'))
            || ($showNav('roles') && $can('roles.view'))
            || ($showNav('settings') && $can('settings.view'))
        ): ?>

            <div class="sidebar-section">
                System
            </div>


            <!-- USERS -->

            <?php if ($showNav('users') && $can('users.view')): ?>

                <a
                    href="<?= BASE_URL ?>/users"
                    class="sidebar-link <?= $isActive('/users') ? 'active' : '' ?>"
                >

                    <i data-lucide="user-cog"></i>

                    <span>
                        Users
                    </span>

                </a>

            <?php endif; ?>


            <!-- ROLES -->

            <?php if ($showNav('roles') && $can('roles.view')): ?>

                <a
                    href="<?= BASE_URL ?>/roles"
                    class="sidebar-link <?= $isActive('/roles') ? 'active' : '' ?>"
                >

                    <i data-lucide="shield-check"></i>

                    <span>
                        Roles & Permissions
                    </span>

                </a>

            <?php endif; ?>


            <!-- SETTINGS -->

            <?php if ($showNav('settings') && $can('settings.view')): ?>

                <a
                    href="<?= BASE_URL ?>/settings"
                    class="sidebar-link <?= $isActive('/settings') ? 'active' : '' ?>"
                >

                    <i data-lucide="settings"></i>

                    <span>
                        Settings
                    </span>

                </a>

            <?php endif; ?>


        <?php endif; ?>


    </nav>

</aside>


<!-- MOBILE OVERLAY -->

<div
    id="sidebarOverlay"
    class="sidebar-overlay"
></div>

<?php endif; ?>


<!-- =========================================================
     MAIN APPLICATION
========================================================= -->

<div class="app-main">


    <?php if (Auth::check()): ?>

        <!-- TOPBAR -->

        <header class="app-topbar">


            <div class="topbar-left">

                <!-- MOBILE MENU -->

                <button
                    type="button"
                    id="mobileMenuBtn"
                    class="mobile-menu-btn"
                >

                    <i data-lucide="menu"></i>

                </button>


                <div class="topbar-title">

                    <?= htmlspecialchars(APP_NAME) ?>

                </div>

            </div>


            <!-- USER -->

            <div class="topbar-user">


                <div class="text-end">

                    <div class="topbar-user-name">

                        <?= htmlspecialchars(
                            Auth::user()['full_name']
                        ) ?>

                    </div>


                    <div class="topbar-user-role">

                        <?= htmlspecialchars(
                            implode(
                                ', ',
                                Auth::roles()
                            )
                        ) ?>

                    </div>

                </div>

                <div class="d-flex align-items-center gap-1">

                    <form method="POST" action="<?= BASE_URL ?>/attendance/clock-in">
                        <button
                            type="submit"
                            class="btn btn-outline-success btn-sm"
                            title="Clock in"
                        >
                            <i data-lucide="play"></i>
                            <span class="d-none d-lg-inline">Clock in</span>
                        </button>
                    </form>

                    <form method="POST" action="<?= BASE_URL ?>/attendance/clock-out">
                        <button
                            type="submit"
                            class="btn btn-outline-warning btn-sm"
                            title="Clock out"
                        >
                            <i data-lucide="square"></i>
                            <span class="d-none d-lg-inline">Clock out</span>
                        </button>
                    </form>

                </div>


                <a
                    href="<?= BASE_URL ?>/logout"
                    class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1"
                >

                    <i data-lucide="log-out"></i>

                    <span class="d-none d-md-inline">
                        Logout
                    </span>

                </a>

            </div>


        </header>


    <?php endif; ?>


    <!-- PAGE CONTENT -->

    <main class="app-content">

        <?php
        $breadcrumbLabels = [
            'dashboard' => 'Dashboard',
            'users' => 'Users',
            'roles' => 'Roles & Permissions',
            'settings' => 'Settings',
            'departments' => 'Departments',
            'positions' => 'Positions',
            'employees' => 'Employees',
            'schedules' => 'Schedules',
            'attendance' => 'Attendance',
            'payroll' => 'Payroll',
            'reports' => 'Reports'
        ];

        $breadcrumbPath = trim(
            substr($currentPath, strlen(trim(BASE_URL, '/'))),
            '/'
        );

        $breadcrumbParts = $breadcrumbPath === ''
            ? []
            : explode('/', $breadcrumbPath);

        $breadcrumbRoot = $breadcrumbParts[0] ?? '';
        ?>

        <?php if (isset($breadcrumbLabels[$breadcrumbRoot])): ?>

            <nav aria-label="breadcrumb" class="mb-3">

                <ol class="breadcrumb mb-0 small">

                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/dashboard">Home</a>
                    </li>

                    <?php foreach ($breadcrumbParts as $index => $part): ?>

                        <?php
                        $label = $breadcrumbLabels[$part]
                            ?? ucwords(str_replace(['-', '_'], ' ', $part));
                        $isLast = $index === count($breadcrumbParts) - 1;
                        ?>

                        <li
                            class="breadcrumb-item <?= $isLast ? 'active' : '' ?>"
                            <?= $isLast ? 'aria-current="page"' : '' ?>
                        >
                            <?= htmlspecialchars($label) ?>
                        </li>

                    <?php endforeach; ?>

                </ol>

            </nav>

        <?php endif; ?>

        <?php require $contentView; ?>

        <div class="async-skeleton" aria-hidden="true"></div>

    </main>

    <footer class="app-footer">
        <span>
            &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?>
        </span>

        <span class="text-muted">
            Attendance and payroll management
        </span>
    </footer>


</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script
    src="<?= BASE_URL ?>/assets/vendor/bootstrap/bootstrap.bundle.min.js"
></script>


<script
    src="<?= BASE_URL ?>/assets/vendor/lucide.min.js"
></script>


<script
    src="<?= BASE_URL ?>/assets/js/icons.js"
></script>

<script
    src="<?= BASE_URL ?>/assets/js/async.js"
></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const sidebar =
            document.getElementById('appSidebar');

        const overlay =
            document.getElementById('sidebarOverlay');

        const menuButton =
            document.getElementById('mobileMenuBtn');


        function openSidebar() {

            if (!sidebar) {
                return;
            }

            sidebar.classList.add('show');

            if (overlay) {
                overlay.classList.add('show');
            }

        }


        function closeSidebar() {

            if (!sidebar) {
                return;
            }

            sidebar.classList.remove('show');

            if (overlay) {
                overlay.classList.remove('show');
            }

        }


        if (menuButton) {

            menuButton.addEventListener(
                'click',
                openSidebar
            );

        }


        if (overlay) {

            overlay.addEventListener(
                'click',
                closeSidebar
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Close mobile sidebar after navigation
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll('.app-sidebar a')
            .forEach(function (link) {

                link.addEventListener(
                    'click',
                    closeSidebar
                );

            });

    }
);

</script>


</body>

</html>
