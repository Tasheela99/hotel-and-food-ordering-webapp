-- Create the database
CREATE DATABASE IF NOT EXISTS restaurant_management_system;
USE restaurant_management_system;

-- Table for users
CREATE TABLE users
(
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)        NOT NULL,
    email      VARCHAR(100) UNIQUE NOT NULL,
    phone      VARCHAR(15)         NOT NULL,
    password   VARCHAR(255)        NOT NULL,
    role       ENUM ('ADMIN', 'USER','HOTEL') DEFAULT 'USER',
    created_at TIMESTAMP                      DEFAULT CURRENT_TIMESTAMP
);

-- Table for tables (for booking)
CREATE TABLE restaurant_tables
(
    table_id     INT AUTO_INCREMENT PRIMARY KEY,
    table_number INT NOT NULL UNIQUE,
    capacity     INT NOT NULL,
    is_available BOOLEAN DEFAULT TRUE
);

-- Table for table bookings
CREATE TABLE table_bookings
(
    booking_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT  NOT NULL,
    table_id     INT  NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status       ENUM ('PENDING', 'CONFIRMED', 'CANCELLED') DEFAULT 'PENDING',
    created_at   TIMESTAMP                                  DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id),
    FOREIGN KEY (table_id) REFERENCES restaurant_tables (table_id)
);

-- Table for food items
CREATE TABLE food_items
(
    food_id      INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100)   NOT NULL,
    description  TEXT,
    price        DECIMAL(10, 2) NOT NULL,
    category     VARCHAR(50)    NOT NULL,
    is_available BOOLEAN   DEFAULT TRUE,
    image        VARCHAR(750),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for orders
CREATE TABLE orders
(
    order_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT            NOT NULL,
    order_date   TIMESTAMP                                                 DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status       ENUM ('PENDING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING',
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

-- Table for order details
CREATE TABLE order_details
(
    order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT            NOT NULL,
    food_id         INT            NOT NULL,
    quantity        INT            NOT NULL,
    price           DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (order_id),
    FOREIGN KEY (food_id) REFERENCES food_items (food_id)
);

-- Table for payments
CREATE TABLE payments
(
    payment_id     INT AUTO_INCREMENT PRIMARY KEY,
    order_id       INT                                                          NOT NULL,
    payment_date   TIMESTAMP                               DEFAULT CURRENT_TIMESTAMP,
    amount         DECIMAL(10, 2)                                               NOT NULL,
    payment_method ENUM ('CREDIT_CARD', 'DEBIT_CARD', 'CASH', 'ONLINE_PAYMENT') NOT NULL,
    status         ENUM ('PENDING', 'COMPLETED', 'FAILED') DEFAULT 'PENDING',
    FOREIGN KEY (order_id) REFERENCES orders (order_id)
);

-- Table for cart
CREATE TABLE cart
(
    cart_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    food_id    INT NOT NULL,
    quantity   INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id),
    FOREIGN KEY (food_id) REFERENCES food_items (food_id)
);

CREATE TABLE order_tracking
(
    tracking_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT NOT NULL,
    status      ENUM ('PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED') DEFAULT 'PENDING',
    updated_at  TIMESTAMP                                                           DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders (order_id)
);

CREATE TABLE hotels
(
    hotel_id   INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    location   VARCHAR(255) NOT NULL,
    image      VARCHAR(750),
    contact    VARCHAR(15)  NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id    INT          NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

CREATE TABLE hotel_bookings
(
    booking_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT  NOT NULL,
    hotel_id     INT  NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    number_of_people        INT            NOT NULL,
    status       ENUM ('PENDING', 'CONFIRMED', 'CANCELLED') DEFAULT 'PENDING',
    created_at   TIMESTAMP                                  DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id),
    FOREIGN KEY (hotel_id) REFERENCES hotels (hotel_id)
);