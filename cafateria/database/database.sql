-- Use DB
CREATE DATABASE IF NOT EXISTS campus_cafeteria;
USE campus_cafeteria;

-- Create tables only if they don't already exist
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'student') DEFAULT 'student',
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    availability ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_name VARCHAR(100) NOT NULL,
    student_email VARCHAR(100) NOT NULL,
    student_phone VARCHAR(20),
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Sample data inserts (only if you want them)
INSERT IGNORE INTO categories (id, name, description) VALUES 
(1, 'Breakfast', 'Morning meals and breakfast items'),
(2, 'Lunch', 'Lunch specials and main courses'),
(3, 'Beverages', 'Hot and cold drinks'),
(4, 'Snacks', 'Quick bites and snacks'),
(5, 'Desserts', 'Sweet treats and desserts');

INSERT IGNORE INTO menu_items (id, category_id, name, description, price, availability) VALUES 
(1, 1, 'Egg Sandwich', 'Fresh eggs with cheese and vegetables', 15.00, 'available'),
(2, 1, 'Pancakes', 'Fluffy pancakes with maple syrup', 20.00, 'available'),
(3, 2, 'Chicken Rice', 'Grilled chicken with seasoned rice', 35.00, 'available'),
(4, 2, 'Beef Burger', 'Juicy beef burger with fries', 40.00, 'available'),
(5, 3, 'Fresh Orange Juice', 'Freshly squeezed orange juice', 10.00, 'available'),
(6, 3, 'Coffee', 'Hot freshly brewed coffee', 8.00, 'available'),
(7, 4, 'Chips', 'Crispy potato chips', 5.00, 'available'),
(8, 4, 'Samosa', 'Crispy vegetable samosa', 3.00, 'available'),
(9, 5, 'Chocolate Cake', 'Rich chocolate cake slice', 15.00, 'available'),
(10, 5, 'Ice Cream', 'Vanilla ice cream cup', 12.00, 'available');

-- --------------------------------------------------------
-- Now handle admin: remove any existing admin (case-insensitive), then insert the new one
-- (be careful: this deletes any existing admin row)
-- --------------------------------------------------------

DELETE FROM users WHERE LOWER(username) = 'admin';

INSERT INTO users (username, password, email, role, full_name) VALUES 
('ADMIN', '123456', 'admin@cafeteria.com', 'admin', 'System Administrator');
