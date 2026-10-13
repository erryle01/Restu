<?php

require_once BASE_PATH . '/modules/Products/Models/ProductsModel.php';

class InventoryController extends Controller
{
    private ProductsModel $products;

    public function __construct()
    {
        $this->products = new ProductsModel();
    }

    public function index(): void
    {
        Auth::requirePermission('inventory.view');
        $this->view('modules/Inventory/Views/index', [
            'products' => $this->products->getAll(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function adjust(int $id): void
    {
        Auth::requirePermission('inventory.adjust');
        $product = $this->products->find($id);
        if (!$product) {
            Session::flash('error', 'Product not found.');
            $this->redirect('/inventory');
        }
        $quantity = (float) ($_POST['quantity'] ?? 0);
        $type = $_POST['movement_type'] ?? 'adjustment';
        if ($quantity == 0 || !in_array($type, ['purchase', 'adjustment', 'return'], true)) {
            Session::flash('error', 'Enter a valid stock quantity and movement type.');
            $this->redirect('/inventory');
        }
        try {
            $this->products->adjustStock($id, $quantity, $type, Auth::id(), trim($_POST['notes'] ?? ''));
            Session::flash('success', 'Inventory updated successfully.');
        } catch (Throwable $exception) {
            Session::flash('error', $exception->getMessage());
        }
        $this->redirect('/inventory');
    }
}
