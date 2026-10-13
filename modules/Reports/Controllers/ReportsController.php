<?php

require_once BASE_PATH . '/modules/Reports/Models/ReportsModel.php';

class ReportsController extends Controller
{
    public function sales(): void
    {
        Auth::requirePermission('pos.view');

        $dateFrom = trim($_GET['date_from'] ?? date('Y-m-01'));
        $dateTo = trim($_GET['date_to'] ?? date('Y-m-d'));

        if (!$this->validDate($dateFrom)) {
            $dateFrom = date('Y-m-01');
        }
        if (!$this->validDate($dateTo)) {
            $dateTo = date('Y-m-d');
        }
        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $model = new ReportsModel();
        $perPage = 25;
        $totalOrders = $model->countOrders($dateFrom, $dateTo);
        $totalPages = max(1, (int) ceil($totalOrders / $perPage));
        $page = min(max(1, (int) ($_GET['page'] ?? 1)), $totalPages);

        $this->view('modules/Reports/Views/sales', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'summary' => $model->getSummary($dateFrom, $dateTo),
            'payments' => $model->getPaymentBreakdown($dateFrom, $dateTo),
            'topProducts' => $model->getTopProducts($dateFrom, $dateTo),
            'orders' => $model->getOrders($dateFrom, $dateTo, $page, $perPage),
            'page' => $page,
            'perPage' => $perPage,
            'totalOrders' => $totalOrders,
            'totalPages' => $totalPages
        ]);
    }

    private function validDate(string $date): bool
    {
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
