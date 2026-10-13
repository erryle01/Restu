CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock_quantity DECIMAL(12,3) NOT NULL DEFAULT 0,
    low_stock_threshold DECIMAL(12,3) NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX products_category_index (category),
    INDEX products_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    movement_type ENUM('purchase','sale','adjustment','void','return') NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    stock_before DECIMAL(12,3) NOT NULL,
    stock_after DECIMAL(12,3) NOT NULL,
    reference_type VARCHAR(50) NULL,
    reference_id BIGINT UNSIGNED NULL,
    notes VARCHAR(255) NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT inventory_movements_product_foreign FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT inventory_movements_user_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX inventory_movements_product_index (product_id),
    INDEX inventory_movements_created_index (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS restaurant_tables (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(30) NOT NULL UNIQUE,
    capacity TINYINT UNSIGNED NOT NULL DEFAULT 2,
    status ENUM('available','occupied','reserved','inactive') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(40) NOT NULL UNIQUE,
    order_type ENUM('dine_in','take_out') NOT NULL,
    table_id INT UNSIGNED NULL,
    status ENUM('open','paid','void','refunded') NOT NULL DEFAULT 'open',
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_method ENUM('cash','card','gcash','other') NULL,
    amount_tendered DECIMAL(12,2) NULL,
    change_amount DECIMAL(12,2) NULL,
    notes VARCHAR(255) NULL,
    cashier_id INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL,
    CONSTRAINT orders_table_foreign FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE SET NULL,
    CONSTRAINT orders_cashier_foreign FOREIGN KEY (cashier_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX orders_status_index (status),
    INDEX orders_created_index (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    line_total DECIMAL(12,2) NOT NULL,
    CONSTRAINT order_items_order_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT order_items_product_foreign FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX order_items_order_index (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO restaurant_tables (table_number, capacity)
SELECT 'T1', 2 WHERE NOT EXISTS (SELECT 1 FROM restaurant_tables WHERE table_number = 'T1');
INSERT INTO restaurant_tables (table_number, capacity)
SELECT 'T2', 2 WHERE NOT EXISTS (SELECT 1 FROM restaurant_tables WHERE table_number = 'T2');
INSERT INTO restaurant_tables (table_number, capacity)
SELECT 'T3', 4 WHERE NOT EXISTS (SELECT 1 FROM restaurant_tables WHERE table_number = 'T3');
INSERT INTO restaurant_tables (table_number, capacity)
SELECT 'T4', 4 WHERE NOT EXISTS (SELECT 1 FROM restaurant_tables WHERE table_number = 'T4');
INSERT INTO restaurant_tables (table_number, capacity)
SELECT 'T5', 6 WHERE NOT EXISTS (SELECT 1 FROM restaurant_tables WHERE table_number = 'T5');
INSERT INTO restaurant_tables (table_number, capacity)
SELECT 'T6', 8 WHERE NOT EXISTS (SELECT 1 FROM restaurant_tables WHERE table_number = 'T6');
