<?php

class ProductsModel extends Model
{
    public function getAll(bool $activeOnly = false): array
    {
        $where = $activeOnly ? "WHERE p.status = 'active'" : '';
        return $this->db->query("SELECT p.*, p.category_id AS category, COALESCE(i.quantity, 0) AS stock_quantity, COALESCE(i.minimum_stock, 0) AS low_stock_threshold FROM products p LEFT JOIN inventory i ON i.product_id = p.id {$where} ORDER BY p.name ASC")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, COALESCE(i.quantity, 0) AS stock_quantity, COALESCE(i.minimum_stock, 0) AS low_stock_threshold FROM products p LEFT JOIN inventory i ON i.product_id = p.id WHERE p.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    public function findBySku(string $sku, ?int $excludeId = null): ?array
    {
        $sql = 'SELECT p.* FROM products p WHERE p.sku = ?';
        $params = [$sku];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO products (sku, name, image_path, price, cost, track_inventory, status) VALUES (?, ?, ?, ?, ?, 1, ?)');
        $stmt->execute([
            $data['sku'], $data['name'], $data['image_path'] ?? null,
            $data['price'], $data['cost'], $data['status']
        ]);
        $id = (int) $this->db->lastInsertId();
        $inventory = $this->db->prepare('INSERT INTO inventory (product_id, quantity, minimum_stock, maximum_stock, updated_at) VALUES (?, ?, ?, 0, NOW())');
        $inventory->execute([$id, $data['stock_quantity'], $data['low_stock_threshold']]);
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE products SET sku = ?, name = ?, image_path = ?, price = ?, cost = ?, status = ? WHERE id = ?');
        $result = $stmt->execute([$data['sku'], $data['name'], $data['image_path'] ?? null, $data['price'], $data['cost'], $data['status'], $id]);
        $inventory = $this->db->prepare('UPDATE inventory SET minimum_stock = ?, updated_at = NOW() WHERE product_id = ?');
        $inventory->execute([$data['low_stock_threshold'], $id]);
        return $result;
    }

    public function adjustStock(int $id, float $quantity, string $type, ?int $userId, string $notes = ''): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT quantity FROM inventory WHERE product_id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $before = $stmt->fetchColumn();
            if ($before === false) {
                $before = 0;
                $createInventory = $this->db->prepare('INSERT INTO inventory (product_id, quantity, minimum_stock, maximum_stock, updated_at) VALUES (?, 0, 0, 0, NOW())');
                $createInventory->execute([$id]);
            }
            $before = (float) $before;
            $after = $before + $quantity;
            if ($after < 0) {
                throw new RuntimeException('Stock cannot be negative.');
            }
            $update = $this->db->prepare('UPDATE inventory SET quantity = ?, updated_at = NOW() WHERE product_id = ?');
            $update->execute([$after, $id]);
            $movement = $this->db->prepare('INSERT INTO inventory_movements (product_id, movement_type, quantity, stock_before, stock_after, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $movement->execute([$id, $type, $quantity, $before, $after, $notes ?: null, $userId]);
            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }
}
