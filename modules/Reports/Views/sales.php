<?php
$e = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$summary = $summary ?? [];
$payments = $payments ?? [];
$topProducts = $topProducts ?? [];
$orders = $orders ?? [];
$page = (int) ($page ?? 1);
$perPage = (int) ($perPage ?? 25);
$totalOrders = (int) ($totalOrders ?? count($orders));
$totalPages = max(1, (int) ($totalPages ?? 1));
$firstOrder = $totalOrders > 0 ? (($page - 1) * $perPage) + 1 : 0;
$lastOrder = min($page * $perPage, $totalOrders);
$pageStart = max(1, $page - 2);
$pageEnd = min($totalPages, $page + 2);
?>
<style>
    .sales-report { --report-ink: #182521; --report-muted: #6a7974; --report-line: #dce5e1; color: var(--report-ink); }
    .sales-report .report-metric { border: 1px solid var(--report-line); border-radius: 6px; background: #fff; height: 100%; }
    .sales-report .report-metric-value { font-size: 26px; font-weight: 700; }
    .sales-report .report-section-title { font-size: 15px; font-weight: 650; }
</style>
<div class="container-fluid py-4 sales-report">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="text-muted small">BUSINESS REPORTS</div>
            <h1 class="h3 mb-1">Sales and income</h1>
            <p class="text-muted mb-0">Paid POS orders for the selected date range.</p>
        </div>
        <a href="<?= BASE_URL ?>/pos" class="btn btn-outline-secondary"><i data-lucide="arrow-left"></i> Back to POS</a>
    </div>

    <form method="GET" action="<?= BASE_URL ?>/reports/sales" data-native-submit class="row g-2 align-items-end mb-4">
        <div class="col-sm-4 col-lg-3"><label class="form-label" for="reportFrom">From</label><input class="form-control" type="date" id="reportFrom" name="date_from" value="<?= $e($dateFrom) ?>" required></div>
        <div class="col-sm-4 col-lg-3"><label class="form-label" for="reportTo">To</label><input class="form-control" type="date" id="reportTo" name="date_to" value="<?= $e($dateTo) ?>" required></div>
        <div class="col-sm-4 col-lg-2"><button class="btn btn-primary w-100" type="submit"><i data-lucide="filter"></i> Apply</button></div>
    </form>

    <div class="row g-3 mb-4">
        <?php foreach ([
            ['label' => 'Income', 'value' => $summary['income'] ?? 0, 'icon' => 'banknote', 'color' => 'success'],
            ['label' => 'Paid orders', 'value' => $summary['order_count'] ?? 0, 'icon' => 'receipt', 'color' => 'primary'],
            ['label' => 'Average order', 'value' => $summary['average_order'] ?? 0, 'icon' => 'chart-no-axes-combined', 'color' => 'info'],
            ['label' => 'Discounts', 'value' => $summary['discounts'] ?? 0, 'icon' => 'tags', 'color' => 'warning']
        ] as $metric): ?>
            <div class="col-6 col-xl-3"><div class="report-metric p-3"><div class="d-flex justify-content-between align-items-start"><span class="text-muted small"><?= $e($metric['label']) ?></span><i class="text-<?= $e($metric['color']) ?>" data-lucide="<?= $e($metric['icon']) ?>"></i></div><div class="report-metric-value mt-2"><?= $metric['label'] === 'Paid orders' ? number_format((int) $metric['value']) : number_format((float) $metric['value'], 2) ?></div></div></div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white report-section-title">Payment methods</div>
                <div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th>Method</th><th class="text-end">Orders</th><th class="text-end">Income</th></tr></thead><tbody>
                    <?php foreach ($payments as $payment): ?><tr><td><?= $e(ucfirst(str_replace('_', ' ', $payment['payment_method']))) ?></td><td class="text-end"><?= (int) $payment['order_count'] ?></td><td class="text-end"><?= number_format((float) $payment['income'], 2) ?></td></tr><?php endforeach; ?>
                    <?php if (!$payments): ?><tr><td colspan="3" class="text-center text-muted py-4">No paid orders for this period.</td></tr><?php endif; ?>
                </tbody></table></div>
            </section>
        </div>
        <div class="col-lg-7">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white report-section-title">Top products by quantity</div>
                <div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th>Product</th><th class="text-end">Qty sold</th><th class="text-end">Sales</th></tr></thead><tbody>
                    <?php foreach ($topProducts as $product): ?><tr><td class="fw-semibold"><?= $e($product['product_name']) ?></td><td class="text-end"><?= number_format((float) $product['quantity_sold'], 3) ?></td><td class="text-end"><?= number_format((float) $product['sales_total'], 2) ?></td></tr><?php endforeach; ?>
                    <?php if (!$topProducts): ?><tr><td colspan="3" class="text-center text-muted py-4">No product sales for this period.</td></tr><?php endif; ?>
                </tbody></table></div>
            </section>
        </div>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center"><span class="report-section-title">Paid order ledger</span><span class="text-muted small">Showing <?= $firstOrder ?>–<?= $lastOrder ?> of <?= number_format($totalOrders) ?> · <?= $perPage ?> per page</span></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Order</th><th>Completed</th><th>Type / table</th><th>Cashier</th><th>Payment</th><th class="text-end">Total</th></tr></thead><tbody>
            <?php foreach ($orders as $order): ?><tr><td class="fw-semibold"><?= $e($order['order_number']) ?></td><td><?= $e($order['completed_at']) ?></td><td><?= $e(ucwords(str_replace('_', ' ', $order['order_type']))) ?><?= !empty($order['table_number']) ? ' · ' . $e($order['table_number']) : '' ?></td><td><?= $e($order['cashier_name'] ?? '—') ?></td><td><?= $e(ucfirst($order['payment_method'] ?? 'Unknown')) ?></td><td class="text-end fw-semibold"><?= number_format((float) $order['total'], 2) ?></td></tr><?php endforeach; ?>
            <?php if (!$orders): ?><tr><td colspan="6" class="text-center text-muted py-5">No paid orders for this period.</td></tr><?php endif; ?>
        </tbody></table></div>
        <?php if ($totalPages > 1): ?>
            <nav class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2" aria-label="Sales report pages">
                <span class="small text-muted">Page <?= $page ?> of <?= $totalPages ?></span>
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $pageUrl = static function (int $targetPage) use ($dateFrom, $dateTo): string {
                        return BASE_URL . '/reports/sales?' . http_build_query([
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                            'page' => $targetPage
                        ]);
                    };
                    ?>
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <?php if ($page <= 1): ?><span class="page-link">Previous</span><?php else: ?><a class="page-link" href="<?= $e($pageUrl($page - 1)) ?>">Previous</a><?php endif; ?>
                    </li>
                    <?php for ($number = $pageStart; $number <= $pageEnd; $number++): ?>
                        <li class="page-item <?= $number === $page ? 'active' : '' ?>">
                            <?php if ($number === $page): ?><span class="page-link" aria-current="page"><?= $number ?></span><?php else: ?><a class="page-link" href="<?= $e($pageUrl($number)) ?>"><?= $number ?></a><?php endif; ?>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <?php if ($page >= $totalPages): ?><span class="page-link">Next</span><?php else: ?><a class="page-link" href="<?= $e($pageUrl($page + 1)) ?>">Next</a><?php endif; ?>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </section>
</div>
