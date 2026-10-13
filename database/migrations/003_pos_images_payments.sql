ALTER TABLE products
    ADD COLUMN image_path VARCHAR(255) NULL AFTER name;

ALTER TABLE orders
    ADD COLUMN payment_method VARCHAR(30) NULL AFTER total,
    ADD COLUMN amount_tendered DECIMAL(12,2) NULL AFTER payment_method,
    ADD COLUMN change_amount DECIMAL(12,2) NULL AFTER amount_tendered,
    ADD COLUMN notes VARCHAR(255) NULL AFTER change_amount,
    ADD COLUMN paid_at TIMESTAMP NULL AFTER notes;
