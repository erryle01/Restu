<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/config/config.php';
require_once BASE_PATH . '/core/Database.php';

$db = Database::getInstance();
$unitId = (int) $db->query("SELECT id FROM units WHERE abbreviation = 'pc' AND status = 'active' ORDER BY id LIMIT 1")->fetchColumn();
if ($unitId <= 0) {
    fwrite(STDERR, "No active Piece (pc) unit exists.\n");
    exit(1);
}

$products = [
    [
        'sku' => 'DEMO-ADOBO-001',
        'name' => 'Chicken Adobo',
        'category' => 'Filipino Favorites',
        'image' => 'uploads/products/cbc7b72982953ff9697862e877270939.webp',
        'description' => 'Classic Filipino chicken adobo with rice.',
        'price' => 225.00,
        'cost' => 95.00,
        'stock' => 18.000
    ],
    [
        'sku' => 'DEMO-BURGER-001',
        'name' => 'Beef Burger',
        'category' => 'Burgers',
        'image' => 'uploads/products/demo_burger.jpg',
        'description' => 'Grilled beef burger with lettuce and tomato.',
        'price' => 195.00,
        'cost' => 82.00,
        'stock' => 22.000
    ],
    [
        'sku' => 'DEMO-PIZZA-001',
        'name' => 'Pepperoni Pizza',
        'category' => 'Pizza',
        'image' => 'uploads/products/demo_pizza.jpg',
        'description' => 'Pepperoni pizza with mozzarella and tomato sauce.',
        'price' => 349.00,
        'cost' => 155.00,
        'stock' => 12.000
    ],
    [
        'sku' => 'DEMO-COFFEE-001',
        'name' => 'Iced Coffee',
        'category' => 'Drinks',
        'image' => 'uploads/products/demo_iced_coffee.jpg',
        'description' => 'Chilled coffee served over ice.',
        'price' => 95.00,
        'cost' => 32.00,
        'stock' => 30.000
    ]
];

$db->beginTransaction();
$created = [];
$skipped = [];

try {
    foreach ($products as $product) {
        if (!is_file(BASE_PATH . '/' . $product['image'])) {
            throw new RuntimeException('Product photo missing: ' . $product['image']);
        }

        $categoryLookup = $db->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
        $categoryLookup->execute([$product['category']]);
        $categoryId = $categoryLookup->fetchColumn();

        if ($categoryId === false) {
            $insertCategory = $db->prepare("INSERT INTO categories (name, description, status) VALUES (?, 'Demo POS menu category', 'active')");
            $insertCategory->execute([$product['category']]);
            $categoryId = (int) $db->lastInsertId();
        }

        $existingProduct = $db->prepare('SELECT id FROM products WHERE sku = ? LIMIT 1');
        $existingProduct->execute([$product['sku']]);
        if ($existingProduct->fetchColumn()) {
            $skipped[] = $product['sku'];
            continue;
        }

        $insertProduct = $db->prepare("INSERT INTO products (category_id, unit_id, sku, name, image_path, description, price, cost, track_inventory, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'active')");
        $insertProduct->execute([
            $categoryId,
            $unitId,
            $product['sku'],
            $product['name'],
            $product['image'],
            $product['description'],
            $product['price'],
            $product['cost']
        ]);
        $productId = (int) $db->lastInsertId();

        $inventory = $db->prepare('INSERT INTO inventory (product_id, quantity, minimum_stock, maximum_stock) VALUES (?, ?, 5, NULL)');
        $inventory->execute([$productId, $product['stock']]);
        $created[] = $product['name'];
    }

    $db->commit();
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, 'Demo food seed failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

echo 'Created: ' . ($created ? implode(', ', $created) : 'none') . PHP_EOL;
echo 'Already existed: ' . ($skipped ? implode(', ', $skipped) : 'none') . PHP_EOL;
echo 'Open ' . BASE_URL . '/pos to view demo menu items.' . PHP_EOL;
