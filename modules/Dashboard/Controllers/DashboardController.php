<?php

require_once BASE_PATH .
    '/modules/Dashboard/Models/DashboardModel.php';


class DashboardController extends Controller
{
    private DashboardModel $dashboardModel;


    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }


    /**
     * Dashboard page.
     */
    public function index(): void
    {
        Auth::requirePermission(
            'dashboard.view'
        );


        /*
         * Dashboard statistics.
         */
        $totalUsers =
            $this->dashboardModel->getTotalUsers();

        $activeUsers =
            $this->dashboardModel->getActiveUsers();

        $inactiveUsers =
            $this->dashboardModel->getInactiveUsers();

        $lockedUsers =
            $this->dashboardModel->getLockedUsers();

        $totalRoles =
            $this->dashboardModel->getTotalRoles();

        $totalPermissions =
            $this->dashboardModel->getTotalPermissions();

        $totalEmployees =
            $this->dashboardModel->getTotalEmployees();

        $todaySales = [];
        $monthSales = [];
        $weeklySales = [];

        if (Auth::can('pos.view')) {
            $today = date('Y-m-d');
            $todaySales = $this->dashboardModel->getSalesSummary($today, $today);
            $monthSales = $this->dashboardModel->getSalesSummary(date('Y-m-01'), $today);
            $weeklySales = $this->dashboardModel->getDailySales(7);
        }


        /*
         * Recent data.
         */
        $recentUsers =
            $this->dashboardModel->getRecentUsers(5);

        $recentActivities =
            $this->dashboardModel->getRecentActivities(10);


        /*
         * Flash messages.
         */
        $success =
            Session::getFlash('success');

        $error =
            Session::getFlash('error');


        /*
         * Render dashboard.
         */
        $this->view(
            'modules/Dashboard/Views/index',
            [
                'totalUsers'       => $totalUsers,
                'activeUsers'      => $activeUsers,
                'inactiveUsers'    => $inactiveUsers,
                'lockedUsers'      => $lockedUsers,
                'totalRoles'       => $totalRoles,
                'totalPermissions' => $totalPermissions,
                'totalEmployees'  => $totalEmployees,
                'todaySales' => $todaySales,
                'monthSales' => $monthSales,
                'weeklySales' => $weeklySales,
                'recentUsers'     => $recentUsers,
                'recentActivities'=> $recentActivities,
                'success'          => $success,
                'error'            => $error
            ]
        );
    }
}