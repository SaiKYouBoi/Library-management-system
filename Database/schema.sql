-- Library Management System Database Schema

DROP DATABASE IF EXISTS library_management;
CREATE DATABASE library_management;
USE library_management;

-- Authors table
CREATE TABLE authors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    biography TEXT,
    nationality VARCHAR(100),
    birth_date DATE,
    death_date DATE,
    primary_genre VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- Library branches table
CREATE TABLE library_branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    operating_hours VARCHAR(100),
    contact_phone VARCHAR(20),
    contact_email VARCHAR(100)
);

-- Books table
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    publication_year INT,
    category_id INT,
    total_copies INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_isbn (isbn),
    INDEX idx_title (title),
    INDEX idx_category (category_id)
);

-- Book-Author relationship (many-to-many)
CREATE TABLE book_authors (
    book_id INT,
    author_id INT,
    PRIMARY KEY (book_id, author_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
);

-- Book inventory per branch
CREATE TABLE book_inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    branch_id INT NOT NULL,
    available_copies INT DEFAULT 0,
    status ENUM('Available', 'Checked Out', 'Reserved', 'Under Maintenance') DEFAULT 'Available',
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES library_branches(id) ON DELETE CASCADE,
    UNIQUE KEY unique_book_branch (book_id, branch_id),
    INDEX idx_book (book_id),
    INDEX idx_branch (branch_id)
);

-- Members table
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_type ENUM('Student', 'Faculty') NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    membership_start DATE NOT NULL,
    membership_end DATE NOT NULL,
    total_borrowed INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_type (member_type)
);

-- Borrow records table
CREATE TABLE borrow_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    branch_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    late_fee DECIMAL(10, 2) DEFAULT 0.00,
    is_renewed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (branch_id) REFERENCES library_branches(id),
    INDEX idx_member (member_id),
    INDEX idx_book (book_id),
    INDEX idx_return (return_date)
);

-- Sample Data
INSERT INTO categories (name, description) VALUES
('Computer Science', 'Programming, algorithms, and software engineering'),
('Literature', 'Fiction, classics, and poetry'),
('Science', 'Physics, chemistry, biology'),
('Mathematics', 'Pure and applied mathematics'),
('History', 'World history and historical events');

INSERT INTO library_branches (name, location, operating_hours, contact_phone, contact_email) VALUES
('Main Campus Library', 'Building A, Central Campus', '8:00 AM - 10:00 PM', '555-0101', 'main@techcity.edu'),
('Engineering Library', 'Engineering Block, East Campus', '8:00 AM - 8:00 PM', '555-0102', 'eng@techcity.edu'),
('Science Library', 'Science Complex, West Campus', '9:00 AM - 9:00 PM', '555-0103', 'science@techcity.edu'),
('Medical Library', 'Medical School, North Campus', '7:00 AM - 11:00 PM', '555-0104', 'medical@techcity.edu'),
('Arts Library', 'Arts Building, South Campus', '10:00 AM - 6:00 PM', '555-0105', 'arts@techcity.edu');

INSERT INTO authors (name, biography, nationality, birth_date, primary_genre) VALUES
('Robert C. Martin', 'Software engineer and author, known as Uncle Bob', 'American', '1952-12-05', 'Computer Science'),
('Martin Fowler', 'British software developer and author', 'British', '1963-12-18', 'Computer Science'),
('George Orwell', 'English novelist and essayist', 'British', '1903-06-25', 'Literature'),
('Isaac Asimov', 'Science fiction author and biochemist', 'American', '1920-01-02', 'Science Fiction'),
('Donald Knuth', 'Computer scientist and mathematician', 'American', '1938-01-10', 'Computer Science');

INSERT INTO books (isbn, title, publication_year, category_id, total_copies) VALUES
('978-0132350884', 'Clean Code', 2008, 1, 15),
('978-0134685991', 'Effective Java', 2018, 1, 10),
('978-0132350882', 'The Pragmatic Programmer', 1999, 1, 12),
('978-0451524935', '1984', 1949, 2, 20),
('978-0553293357', 'Foundation', 1951, 2, 8),
('978-0201896831', 'The Art of Computer Programming Vol 1', 1968, 1, 5);

INSERT INTO book_authors (book_id, author_id) VALUES
(1, 1), (2, 2), (3, 2), (4, 3), (5, 4), (6, 5);

INSERT INTO book_inventory (book_id, branch_id, available_copies, status) VALUES
(1, 1, 5, 'Available'), (1, 2, 3, 'Available'), (1, 3, 2, 'Available'),
(2, 1, 4, 'Available'), (2, 2, 3, 'Available'),
(3, 1, 6, 'Available'), (3, 3, 4, 'Available'),
(4, 1, 10, 'Available'), (4, 4, 5, 'Available'), (4, 5, 5, 'Available'),
(5, 1, 4, 'Available'), (5, 5, 4, 'Available'),
(6, 1, 3, 'Available'), (6, 2, 2, 'Available');

INSERT INTO members (member_type, full_name, email, phone, membership_start, membership_end) VALUES
('Student', 'Alice Johnson', 'alice.j@techcity.edu', '555-1001', '2025-01-01', '2025-12-31'),
('Student', 'Bob Smith', 'bob.s@techcity.edu', '555-1002', '2025-01-01', '2025-12-31'),
('Faculty', 'Dr. Carol Williams', 'carol.w@techcity.edu', '555-2001', '2024-01-01', '2027-01-01'),
('Faculty', 'Prof. David Brown', 'david.b@techcity.edu', '555-2002', '2023-09-01', '2026-09-01'),
('Student', 'Eve Davis', 'eve.d@techcity.edu', '555-1003', '2024-08-01', '2024-12-31');