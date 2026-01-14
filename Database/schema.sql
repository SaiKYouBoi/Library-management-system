CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('student', 'faculty') NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    membership_expiry DATE,
    unpaid_fees DECIMAL(6,2) DEFAULT 0
);

CREATE TABLE books (
    isbn VARCHAR(13) PRIMARY KEY,
    title VARCHAR(150),
    publication_year INT,
    status ENUM('available','checked_out','reserved','maintenance')
);

CREATE TABLE authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    nationality VARCHAR(50),
    genre VARCHAR(50)
);

CREATE TABLE book_author (
    book_isbn VARCHAR(13),
    author_id INT,
    PRIMARY KEY (book_isbn, author_id),
    FOREIGN KEY (book_isbn) REFERENCES books(isbn),
    FOREIGN KEY (author_id) REFERENCES authors(id)
);

CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    location VARCHAR(100)
);

CREATE TABLE branch_inventory (
    branch_id INT,
    book_isbn VARCHAR(13),
    copies INT,
    PRIMARY KEY (branch_id, book_isbn),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (book_isbn) REFERENCES books(isbn)
);

CREATE TABLE borrow_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    book_isbn VARCHAR(13),
    branch_id INT,
    borrow_date DATE,
    due_date DATE,
    return_date DATE NULL,
    late_fee DECIMAL(6,2) DEFAULT 0,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (book_isbn) REFERENCES books(isbn),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);
