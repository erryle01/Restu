<?php

class POSModel extends Model
{
    public function getProducts(): array
    {
        return $this->db->query("SELECT p.id, p.sku, p.name, p.image_path, p.category_id AS category, p.price, COALESCE(i.quantity, 0) AS stock_quantity FROM products p LEFT JOIN inventory i ON i.product_id = p.id WHERE p.status = 'active' ORDER BY p.category_id, p.name")->fetchAll();
    }

    public function getTables(): array
    {
        return $this->db->query("SELECT id, table_number, capacity, status FROM restaurant_tables WHERE status != 'inactive' ORDER BY table_number")->fetchAll();
    }

    public function checkout(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $items = $data['items'];
            $subtotal = 0;
            $lockedItems = [];
            foreach ($items as $item) {
                $stmt = $this->db->prepare("SELECT p.id, p.name, p.price, COALESCE(i.quantity, 0) AS stock_quantity FROM products p LEFT JOIN inventory i ON i.product_id = p.id WHERE p.id = ? AND p.status = 'active' FOR UPDATE");
                $stmt->execute([(int) $item['id']]);
                $product = $stmt->fetch();
                $quantity = (float) ($item['quantity'] ?? 0);
                if (!$product || $quantity <= 0) {
                    throw new RuntimeException('Invalid product or quantity in cart.');
                }
                if ((float) $product['stock_quantity'] < $quantity) {
                    throw new RuntimeException('Not enough stock for ' . $product['name'] . '.');
                }
                $lineTotal = round((float) $product['price'] * $quantity, 2);
                $subtotal += $lineTotal;
                $lockedItems[] = [$product, $quantity, $lineTotal];
            }
            $discount = max(0, (float) ($data['discount'] ?? 0));
            $taxRate = max(0, (float) ($data['tax_rate'] ?? 0));
            $tax = round(max(0, $subtotal - $discount) * ($taxRate / 100), 2);
            $total = round(max(0, $subtotal - $discount) + $tax, 2);
            $tendered = (float) ($data['amount_tendered'] ?? 0);
            if ($tendered < $total) {
                throw new RuntimeException('Amount tendered is less than the order total.');
            }
            if ($data['order_type'] === 'dine_in' && empty($data['table_id'])) {
                throw new RuntimeException('Select a table for dine-in orders.');
            }
            $orderNumber = 'ORD-' . date('YmdHis') . '-' . random_int(100, 999);
            $order = $this->db->prepare("INSERT INTO orders (order_number, order_type, table_id, cashier_id, subtotal, discount, tax, total, payment_method, amount_tendered, change_amount, notes, status, created_at, updated_at, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', NOW(), NOW(), NOW())");
            $order->execute([$orderNumber, $data['order_type'], $data['table_id'] ?: null, $data['cashier_id'], $subtotal, $discount, $tax, $total, $data['payment_method'], $tendered, $tendered - $total, $data['notes'] ?: null]);
            $orderId = (int) $this->db->lastInsertId();
            foreach ($lockedItems as [$product, $quantity, $lineTotal]) {
                $insert = $this->db->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, discount, subtotal, created_at) VALUES (?, ?, ?, ?, ?, 0, ?, NOW())');
                $insert->execute([$orderId, $product['id'], $product['name'], $quantity, $product['price'], $lineTotal]);
                $before = (float) $product['stock_quantity'];
                $after = $before - $quantity;
                $update = $this->db->prepare('UPDATE inventory SET quantity = ?, updated_at = NOW() WHERE product_id = ?');
                $update->execute([$after, $product['id']]);

                if ($update->rowCount() === 0) {
                    $insertInventory = $this->db->prepare('INSERT INTO inventory (product_id, quantity, minimum_stock, maximum_stock, updated_at) VALUES (?, ?, 0, 0, NOW())');
                    $insertInventory->execute([$product['id'], $after]);
                }
                $movement = $this->db->prepare("INSERT INTO inventory_movements (product_id, movement_type, quantity, stock_before, stock_after, reference_type, reference_id, created_by) VALUES (?, 'sale', ?, ?, ?, 'order', ?, ?)");
                $movement->execute([$product['id'], -$quantity, $before, $after, $orderId, $data['cashier_id']]);
            }
            if ($data['order_type'] === 'dine_in' && $data['table_id']) {
                $table = $this->db->prepare("UPDATE restaurant_tables SET status = 'occupied' WHERE id = ?");
                $table->execute([$data['table_id']]);
            }
            $this->db->commit();
            return $orderId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }
}
