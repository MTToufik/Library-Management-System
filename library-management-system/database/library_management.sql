-- =========================================================
-- Library Management System - Database Schema (v2)
-- Now supports: Librarian/Admin login + Student self-registration,
-- login, and book borrow requests.
-- =========================================================
-- Import this file via phpMyAdmin or:
--   mysql -u root -p < library_management.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS library_management;
USE library_management;

-- ---------------------------------------------------------
-- Table: admins  (Admin / Librarian login accounts)
-- ---------------------------------------------------------
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,      -- stored using PHP password_hash()
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('Admin', 'Librarian') NOT NULL DEFAULT 'Librarian',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: books
-- ---------------------------------------------------------
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150) NOT NULL,
    isbn VARCHAR(30) NOT NULL UNIQUE,
    category VARCHAR(100) NOT NULL,
    publisher VARCHAR(150),
    publish_year YEAR,
    total_copies INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    shelf_location VARCHAR(50),
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: members  (Students)
-- Students can self-register (status starts as 'Pending')
-- or be added directly by a librarian (status 'Active').
-- ---------------------------------------------------------
CREATE TABLE members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    member_code VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,       -- stored using PHP password_hash()
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255),
    membership_date DATE NOT NULL,
    status ENUM('Pending', 'Active', 'Inactive', 'Rejected') NOT NULL DEFAULT 'Pending',
    approved_by INT DEFAULT NULL,
    FOREIGN KEY (approved_by) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: issued_books  (Issue / Return records)
-- ---------------------------------------------------------
CREATE TABLE issued_books (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status ENUM('Issued', 'Returned', 'Overdue') NOT NULL DEFAULT 'Issued',
    fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    issued_by INT,                       -- admin_id who issued the book
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: book_requests  (Students request to borrow a book;
-- a librarian approves or rejects each request)
-- ---------------------------------------------------------
CREATE TABLE book_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    request_date DATE NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    processed_by INT DEFAULT NULL,
    processed_date DATE DEFAULT NULL,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- Sample Dummy Data
-- =========================================================

-- Default admin login -> username: admin | password: admin123
-- Bcrypt hash of "admin123" (verifiable with PHP's password_verify())
INSERT INTO admins (username, password, full_name, email, role) VALUES
('admin', '$2b$12$2PvQoZjAnEBwUkCLgKYDluy9NCdUg1dpZY5VjE3P34l56hXatyeRa', 'System Administrator', 'admin@library.com', 'Admin'),
('librarian', '$2b$12$2PvQoZjAnEBwUkCLgKYDluy9NCdUg1dpZY5VjE3P34l56hXatyeRa', 'Jane Librarian', 'librarian@library.com', 'Librarian');
-- NOTE: Both accounts use the password "admin123" for this demo hash.

INSERT INTO books (title, author, isbn, category, publisher, publish_year, total_copies, available_copies, shelf_location) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 'Fiction', 'Scribner', 1925, 5, 3, 'A1-01'),
('To Kill a Mockingbird', 'Harper Lee', '9780061120084', 'Fiction', 'Harper Perennial', 1960, 4, 4, 'A1-02'),
('A Brief History of Time', 'Stephen Hawking', '9780553380163', 'Science', 'Bantam', 1988, 3, 2, 'B2-01'),
('Clean Code', 'Robert C. Martin', '9780132350884', 'Technology', 'Prentice Hall', 2008, 6, 5, 'C3-01'),
('The Pragmatic Programmer', 'Andrew Hunt', '9780201616224', 'Technology', 'Addison-Wesley', 1999, 4, 4, 'C3-02'),
('Sapiens', 'Yuval Noah Harari', '9780062316097', 'History', 'Harper', 2011, 5, 3, 'D4-01'),
('1984', 'George Orwell', '9780451524935', 'Fiction', 'Signet Classic', 1949, 6, 5, 'A1-03'),
('The Alchemist', 'Paulo Coelho', '9780061122415', 'Fiction', 'HarperOne', 1988, 4, 4, 'A1-04');

-- Demo student accounts. Password for ALL of these is: student123
-- Bcrypt hash of "student123" (verifiable with PHP's password_verify())
INSERT INTO members (member_code, full_name, email, password, phone, address, membership_date, status, approved_by) VALUES
('MEM001', 'John Carter', 'john.carter@example.com', '$2b$12$Bz.7L9tB95wZ498/PqdBpulSWABzbqlltYOp1uQ/JgJNnDv3K7TcC', '01711000001', '12 Green Road, Dhaka', '2025-01-15', 'Active', 1),
('MEM002', 'Emily Watson', 'emily.watson@example.com', '$2b$12$Bz.7L9tB95wZ498/PqdBpulSWABzbqlltYOp1uQ/JgJNnDv3K7TcC', '01711000002', '45 Lake View, Dhaka', '2025-02-10', 'Active', 1),
('MEM003', 'Michael Chen', 'michael.chen@example.com', '$2b$12$Bz.7L9tB95wZ498/PqdBpulSWABzbqlltYOp1uQ/JgJNnDv3K7TcC', '01711000003', '78 Park Street, Dhaka', '2025-03-05', 'Active', 2),
('MEM004', 'Sara Ahmed', 'sara.ahmed@example.com', '$2b$12$Bz.7L9tB95wZ498/PqdBpulSWABzbqlltYOp1uQ/JgJNnDv3K7TcC', '01711000004', '23 River Side, Dhaka', '2025-04-20', 'Inactive', 1),
('MEM005', 'Priya Nair', 'priya.nair@example.com', '$2b$12$Bz.7L9tB95wZ498/PqdBpulSWABzbqlltYOp1uQ/JgJNnDv3K7TcC', '01711000005', '9 Hill View, Dhaka', '2026-07-15', 'Pending', NULL);
-- MEM005 shows the self-registration approval flow: log in as admin and approve/reject this account.

INSERT INTO issued_books (book_id, member_id, issue_date, due_date, return_date, status, fine_amount, issued_by) VALUES
(1, 1, '2026-06-20', '2026-07-04', NULL, 'Overdue', 20.00, 1),
(1, 2, '2026-06-25', '2026-07-09', NULL, 'Issued', 0.00, 1),
(3, 3, '2026-06-28', '2026-07-12', NULL, 'Issued', 0.00, 2),
(4, 1, '2026-05-01', '2026-05-15', '2026-05-14', 'Returned', 0.00, 1),
(6, 2, '2026-06-01', '2026-06-15', '2026-06-20', 'Returned', 10.00, 2);

INSERT INTO book_requests (book_id, member_id, request_date, status, processed_by, processed_date) VALUES
(7, 3, '2026-07-18', 'Pending', NULL, NULL),
(2, 1, '2026-07-10', 'Approved', 1, '2026-07-11'),
(5, 4, '2026-07-05', 'Rejected', 2, '2026-07-06');
