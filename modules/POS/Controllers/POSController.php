<?php

require_once BASE_PATH . '/modules/POS/Models/POSModel.php';
require_once BASE_PATH . '/modules/Settings/Models/SettingsModel.php';

class POSController extends Controller
{
    private POSModel $model;

    public function __construct()
    {
        $this->model = new POSModel();
    }

    public function index(): void
    {
        Auth::requirePermission('pos.view');
        $settings = new SettingsModel();
        $this->view('modules/POS/Views/index', [
            'products' => $this->model->getProducts(),
            'tables' => $this->model->getTables(),
            'taxRate' => (float) ($settings->get('tax_rate', 0)),
            'currency' => $settings->get('currency', 'PHP'),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function checkout(): void
    {
        Auth::requirePermission('pos.create');
        $items = json_decode($_POST['cart'] ?? '[]', true);
        if (!is_array($items) || !$items) {
            $this->checkoutError('Add at least one product to the order.');
        }
        $discount = max(0, (float) ($_POST['discount'] ?? 0));
        if ($discount > 0 && !Auth::can('pos.discount')) {
            $this->checkoutError('You do not have permission to apply discounts.');
        }
        $orderType = $_POST['order_type'] ?? 'take_out';
        if (!in_array($orderType, ['dine_in', 'take_out'], true)) {
            $this->checkoutError('Invalid order type.');
        }
        try {
            $id = $this->model->checkout([
                'items' => $items,
                'order_type' => $orderType,
                'table_id' => (int) ($_POST['table_id'] ?? 0),
                'discount' => $discount,
                'tax_rate' => (float) ($_POST['tax_rate'] ?? 0),
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'amount_tendered' => (float) ($_POST['amount_tendered'] ?? 0),
                'notes' => trim($_POST['notes'] ?? ''),
                'cashier_id' => Auth::id()
            ]);

            if (ApiResponse::isRequest()) {
                $this->apiSuccess([
                    'order_id' => $id,
                    'message' => 'Order completed successfully.',
                    'keep_pos_open' => true
                ]);
            }

            Session::flash('success', 'Order completed successfully.');
            $this->redirect('/pos');
        } catch (Throwable $exception) {
            $this->checkoutError($exception->getMessage());
        }
    }

    private function checkoutError(string $message): never
    {
        if (ApiResponse::isRequest()) {
            $this->apiError(422, 'CHECKOUT_FAILED', $message);
        }
        Session::flash('error', $message);
        $this->redirect('/pos');
    }
}
