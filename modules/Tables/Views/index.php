<?php
$e = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$tables = $tables ?? [];
$counts = ['available' => 0, 'occupied' => 0, 'reserved' => 0, 'inactive' => 0];
foreach ($tables as $table) {
	if (isset($counts[$table['status']])) {
		$counts[$table['status']]++;
	}
}
$statusStyles = [
	'available' => ['badge' => 'success', 'icon' => 'circle-check', 'border' => 'border-success'],
	'occupied' => ['badge' => 'warning', 'icon' => 'users-round', 'border' => 'border-warning'],
	'reserved' => ['badge' => 'info', 'icon' => 'clock-3', 'border' => 'border-info'],
	'inactive' => ['badge' => 'secondary', 'icon' => 'circle-slash', 'border' => 'border-secondary']
];
?>
<style>
	.table-management { --table-ink: #17221f; --table-muted: #687873; --table-line: #dce5e1; color: var(--table-ink); }
	.table-management .table-status-summary { border: 1px solid var(--table-line); border-radius: 6px; background: #fff; }
	.table-management .table-status-summary strong { font-size: 24px; line-height: 1; }
	.table-management .table-unit { border: 1px solid var(--table-line); border-top-width: 3px; border-radius: 6px; background: #fff; transition: box-shadow .15s ease, transform .15s ease; }
	.table-management .table-unit:hover { box-shadow: 0 8px 22px rgba(23, 55, 43, .1); transform: translateY(-2px); }
	.table-management .table-number { font-size: 24px; font-weight: 750; }
	.table-management .table-capacity { color: var(--table-muted); font-size: 13px; }
</style>

<div class="container-fluid py-4 table-management">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="text-muted small mb-1">RESTAURANT FLOOR</div>
			<h1 class="h3 mb-1">Table management</h1>
			<p class="text-muted mb-0">Check availability, edit seating, and release finished tables.</p>
		</div>
		<a class="btn btn-primary" href="<?= BASE_URL ?>/tables/create"><i data-lucide="plus"></i> Add table</a>
	</div>

	<?php if (!empty($success)): ?><div class="alert alert-success" role="status"><?= $e($success) ?></div><?php endif; ?>
	<?php if (!empty($error)): ?><div class="alert alert-danger" role="alert"><?= $e($error) ?></div><?php endif; ?>

	<div class="row g-2 mb-4">
		<?php foreach (['available' => 'Available', 'occupied' => 'Occupied', 'reserved' => 'Reserved'] as $key => $label): ?>
			<div class="col-4">
				<div class="table-status-summary p-3 h-100">
					<div class="text-muted small mb-2"><?= $e($label) ?></div>
					<strong><?= (int) $counts[$key] ?></strong>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="row g-3">
		<?php foreach ($tables as $table): ?>
			<?php
			$status = $table['status'] ?? 'inactive';
			$style = $statusStyles[$status] ?? $statusStyles['inactive'];
			?>
			<div class="col-sm-6 col-lg-4 col-xxl-3">
				<article class="table-unit <?= $e($style['border']) ?> p-3 h-100">
					<div class="d-flex justify-content-between align-items-start gap-2">
						<div>
							<div class="table-number"><?= $e($table['table_number']) ?></div>
							<div class="table-capacity"><i data-lucide="users-round"></i> <?= (int) $table['capacity'] ?> seats</div>
						</div>
						<span class="badge text-bg-<?= $e($style['badge']) ?>"><i data-lucide="<?= $e($style['icon']) ?>"></i> <?= $e(ucfirst($status)) ?></span>
					</div>
					<div class="d-flex flex-wrap gap-2 mt-4">
						<a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/tables/edit/<?= (int) $table['id'] ?>"><i data-lucide="pencil"></i> Edit</a>
						<?php if ($status === 'occupied'): ?>
							<form method="POST" action="<?= BASE_URL ?>/tables/release/<?= (int) $table['id'] ?>">
								<button class="btn btn-sm btn-success" type="submit"><i data-lucide="rotate-ccw"></i> Release table</button>
							</form>
						<?php elseif ($status !== 'inactive'): ?>
							<form method="POST" action="<?= BASE_URL ?>/tables/delete/<?= (int) $table['id'] ?>">
								<button class="btn btn-sm btn-outline-danger" type="submit"><i data-lucide="archive"></i> Disable</button>
							</form>
						<?php endif; ?>
					</div>
				</article>
			</div>
		<?php endforeach; ?>
		<?php if (!$tables): ?>
			<div class="col-12"><div class="text-center text-muted py-5">No tables have been configured yet.</div></div>
		<?php endif; ?>
	</div>
</div>
