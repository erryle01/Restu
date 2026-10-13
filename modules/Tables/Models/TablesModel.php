<?php

class TablesModel extends Model
{
    public function getAll(): array
    {
        return $this->db->query('SELECT * FROM restaurant_tables ORDER BY table_number')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM restaurant_tables WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $table = $stmt->fetch();
        return $table ?: null;
    }

    public function findByNumber(string $number, ?int $excludeId = null): ?array
    {
        $sql = 'SELECT * FROM restaurant_tables WHERE table_number = ?';
        $params = [$number];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $table = $stmt->fetch();
        return $table ?: null;
    }

    public function create(string $number, int $capacity): int
    {
        $stmt = $this->db->prepare("INSERT INTO restaurant_tables (table_number, capacity, status) VALUES (?, ?, 'available')");
        $stmt->execute([$number, $capacity]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $number, int $capacity, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE restaurant_tables SET table_number = ?, capacity = ?, status = ? WHERE id = ?');
        return $stmt->execute([$number, $capacity, $status, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE restaurant_tables SET status = 'inactive' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function release(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE restaurant_tables SET status = 'available' WHERE id = ? AND status = 'occupied'");
        return $stmt->execute([$id]) && $stmt->rowCount() === 1;
    }
}
