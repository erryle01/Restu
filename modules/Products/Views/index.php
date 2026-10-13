<?php
$e = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$products = $products ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-1">Products</h1><p class="text-muted mb-0">Menu items, prices, and stock.</p></div>
        <?php if (Auth::can('products.create')): ?><a class="btn btn-primary" href="<?= BASE_URL ?>/products/create"><i data-lucide="plus"></i> Add product</a><?php endif; ?>
    </div>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= $e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= $e($error) ?></div><?php endif; ?>
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>SKU</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <?php $low = (float) $product['stock_quantity'] <= (float) $product['low_stock_threshold']; ?>
            <tr><td><?php if (!empty($product['image_path'])): ?><img src="<?= BASE_URL . '/' . $e($product['image_path']) ?>" alt="" style="width:42px;height:42px;object-fit:cover" class="rounded me-2"><?php endif; ?><?= $e($product['sku'] ?? '') ?></td><td class="fw-semibold"><?= $e($product['name'] ?? '') ?></td><td><?= $e($product['category'] ?? $product['category_id'] ?? '-') ?></td><td><?= number_format((float) ($product['price'] ?? 0), 2) ?></td><td class="<?= $low ? 'text-danger fw-semibold' : '' ?>"><?= $e($product['stock_quantity'] ?? 0) ?></td><td><span class="badge text-bg-<?= ($product['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= $e(ucfirst($product['status'] ?? '')) ?></span></td><td class="text-end"><?php if (Auth::can('products.edit')): ?><a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/products/edit/<?= (int) ($product['id'] ?? 0) ?>"><i data-lucide="pencil"></i></a><?php endif; ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$products): ?><tr><td colspan="7" class="text-center text-muted py-5">No products found.</td></tr><?php endif; ?>
        </tbody>
    </table></div></div>
</div>
