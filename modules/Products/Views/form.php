<?php
$e = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$product = $product ?? [];
?>
<div class="container-fluid py-4"><div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0"><?= $e($title ?? 'Product') ?></h1><a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/products">Back</a></div>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= $e($error) ?></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data" action="<?= $e($action) ?>" class="card border-0 shadow-sm"><div class="card-body"><div class="row g-3">
<div class="col-md-4"><label class="form-label">SKU</label><input class="form-control" name="sku" value="<?= $e($product['sku'] ?? '') ?>"><div class="form-text">Leave blank to generate automatically.</div></div>
<div class="col-md-8"><label class="form-label">Name</label><input class="form-control" name="name" required value="<?= $e($product['name'] ?? '') ?>"></div>
<div class="col-md-4"><label class="form-label">Category</label><input class="form-control" name="category" value="<?= $e($product['category'] ?? '') ?>"></div>
<div class="col-md-4"><label class="form-label">Selling price</label><input class="form-control" type="number" min="0" step="0.01" name="price" value="<?= $e($product['price'] ?? '0') ?>"></div>
<div class="col-md-4"><label class="form-label">Cost</label><input class="form-control" type="number" min="0" step="0.01" name="cost" value="<?= $e($product['cost'] ?? '0') ?>"></div>
<div class="col-md-4"><label class="form-label">Opening stock</label><input class="form-control" type="number" min="0" step="0.001" name="stock_quantity" value="<?= $e($product['stock_quantity'] ?? '0') ?>" <?= !empty($product) ? 'readonly' : '' ?>></div>
<div class="col-md-4"><label class="form-label">Low stock threshold</label><input class="form-control" type="number" min="0" step="0.001" name="low_stock_threshold" value="<?= $e($product['low_stock_threshold'] ?? '0') ?>"></div>
<div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
<div class="col-md-6"><label class="form-label">Product image</label><input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG, or WebP up to 3 MB.</div></div>
<div class="col-12 text-end"><button class="btn btn-primary" type="submit">Save product</button></div>
</div></div></form></div>
