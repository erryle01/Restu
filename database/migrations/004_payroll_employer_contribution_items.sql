ALTER TABLE payroll_items
    MODIFY COLUMN item_type ENUM('earning', 'deduction', 'employer_contribution') NOT NULL;
