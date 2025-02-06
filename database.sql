CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    reg_number VARCHAR(20) NOT NULL UNIQUE,
    sex ENUM('male', 'female', 'other') NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    region VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
