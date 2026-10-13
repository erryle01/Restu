ALTER TABLE users
    ADD COLUMN employee_id INT NULL AFTER full_name,
    ADD UNIQUE KEY users_employee_id_unique (employee_id),
    ADD CONSTRAINT users_employee_id_foreign
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
