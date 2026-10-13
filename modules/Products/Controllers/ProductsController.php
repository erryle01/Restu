<?php

require_once BASE_PATH . '/modules/Products/Models/ProductsModel.php';

class ProductsController extends Controller
{
    private ProductsModel $model;

    public function __construct()
    {
        $this->model = new ProductsModel();
    }

    public function index(): void
    {
        Auth::requirePermission('products.view');
        $this->view('modules/Products/Views/index', [
            'products' => $this->model->getAll(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('products.create');
        $this->view('modules/Products/Views/form', [
            'product' => [],
            'action' => BASE_URL . '/products/create',
            'title' => 'Add Product',
            'error' => Session::getFlash('error')
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('products.create');
        $data = $this->input();
        if ($data['name'] === '') {
            Session::flash('error', 'Product name is required.');
            $this->redirect('/products/create');
        }
        $data['sku'] = $this->generateSku($data['sku'], $data['name']);
        $data['image_path'] = $this->uploadImage();
        if ($this->model->findBySku($data['sku'])) {
            Session::flash('error', 'SKU already exists.');
            $this->redirect('/products/create');
        }
        $this->model->create($data);
        Session::flash('success', 'Product created successfully.');
        $this->redirect('/products');
    }

    public function edit(int $id): void
    {
        Auth::requirePermission('products.edit');
        $product = $this->model->find($id);
        if (!$product) {
            Session::flash('error', 'Product not found.');
            $this->redirect('/products');
        }
        $this->view('modules/Products/Views/form', [
            'product' => $product,
            'action' => BASE_URL . '/products/edit/' . $id,
            'title' => 'Edit Product',
            'error' => Session::getFlash('error')
        ]);
    }

    public function update(int $id): void
    {
        Auth::requirePermission('products.edit');
        $data = $this->input();
        if ($data['name'] === '') {
            Session::flash('error', 'Product name is required.');
            $this->redirect('/products/edit/' . $id);
        }
        $data['sku'] = $this->generateSku($data['sku'], $data['name'], $id);
        $data['image_path'] = $this->uploadImage($this->model->find($id)['image_path'] ?? null);
        if ($this->model->findBySku($data['sku'], $id)) {
            Session::flash('error', 'SKU already exists.');
            $this->redirect('/products/edit/' . $id);
        }
        $this->model->update($id, $data);
        Session::flash('success', 'Product updated successfully.');
        $this->redirect('/products');
    }

    private function input(): array
    {
        return [
            'sku' => trim($_POST['sku'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'price' => max(0, (float) ($_POST['price'] ?? 0)),
            'cost' => max(0, (float) ($_POST['cost'] ?? 0)),
            'stock_quantity' => max(0, (float) ($_POST['stock_quantity'] ?? 0)),
            'low_stock_threshold' => max(0, (float) ($_POST['low_stock_threshold'] ?? 0)),
            'status' => in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active'
        ];
    }

    private function generateSku(string $sku, string $name, ?int $excludeId = null): string
    {
        $sku = strtoupper(trim($sku));

        if ($sku === '') {
            $prefix = preg_replace('/[^A-Z0-9]/', '', strtoupper($name));
            $prefix = substr($prefix ?: 'ITEM', 0, 6);
            do {
                $sku = $prefix . '-' . random_int(1000, 9999);
            } while ($this->model->findBySku($sku, $excludeId));
        }

        return $sku;
    }

    private function uploadImage(?string $existing = null): ?string
    {
        if (empty($_FILES['image']['name'])) {
            return $existing;
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK || $_FILES['image']['size'] > 3 * 1024 * 1024) {
            Session::flash('error', 'Product image must be smaller than 3 MB.');
            $this->redirect('/products');
        }

        $mime = mime_content_type($_FILES['image']['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if (!isset($allowed[$mime])) {
            Session::flash('error', 'Only JPG, PNG, and WebP images are allowed.');
            $this->redirect('/products');
        }

        $directory = BASE_PATH . '/uploads/products';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        move_uploaded_file($_FILES['image']['tmp_name'], $directory . '/' . $filename);

        return 'uploads/products/' . $filename;
    }
}
