CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) UNIQUE NOT NULL,
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  password TEXT,
  role ENUM('superadmin', 'admin') NOT NULL,
  status ENUM('active', 'suspended') DEFAULT 'active',
  date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  product_id INT AUTO_INCREMENT PRIMARY KEY,
  product_name VARCHAR(255) NOT NULL,
  price INT NOT NULL,
  product_image TEXT,
  category ENUM('signatures', 'coffee', 'non-coffee', 'pastries', 'pasta') NOT NULL,
  added_by INT NOT NULL,
  date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (added_by) REFERENCES users(user_id)
);

CREATE TABLE transactions (
  transaction_id INT AUTO_INCREMENT PRIMARY KEY,
  total_amount INT NOT NULL,
  date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE transaction_items (
  item_id INT AUTO_INCREMENT PRIMARY KEY,
  transaction_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  subtotal INT NOT NULL,
  FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id),
  FOREIGN KEY (product_id) REFERENCES products(product_id)
);
