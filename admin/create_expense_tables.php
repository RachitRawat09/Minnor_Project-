<?php
include '../includes/db_connect.php';

// Create expense_categories table
$create_categories_table = "CREATE TABLE IF NOT EXISTS expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Create expenses table
$create_expenses_table = "CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id)
)";

// Insert default categories
$insert_categories = "INSERT IGNORE INTO expense_categories (name, description) VALUES 
    ('Food & Beverages', 'Expenses related to food and drinks'),
    ('Supplies', 'Restaurant supplies and ingredients'),
    ('Utilities', 'Electricity, water, gas, and other utilities'),
    ('Rent', 'Restaurant space rent'),
    ('Equipment', 'Kitchen equipment and maintenance'),
    ('Marketing', 'Advertising and promotional expenses'),
    ('Staff', 'Employee salaries and benefits'),
    ('Other', 'Miscellaneous expenses')";

// Execute queries
if ($conn->query($create_categories_table) === TRUE) {
    echo "Expense categories table created successfully<br>";
} else {
    echo "Error creating categories table: " . $conn->error . "<br>";
}

if ($conn->query($create_expenses_table) === TRUE) {
    echo "Expenses table created successfully<br>";
} else {
    echo "Error creating expenses table: " . $conn->error . "<br>";
}

if ($conn->query($insert_categories) === TRUE) {
    echo "Default categories inserted successfully<br>";
} else {
    echo "Error inserting categories: " . $conn->error . "<br>";
}

$conn->close();
?> 