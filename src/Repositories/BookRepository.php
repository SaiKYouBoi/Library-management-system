<?php

namespace LibraryManagement\Repositories;

use LibraryManagement\Models\Book;
use LibraryManagement\Models\Author;
use PDO;
use DateTime;

class BookRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function findById(int $id): ?Book
    {
        $sql = "SELECT b.*, c.name as category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.id 
                WHERE b.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        $book = $this->createBookFromData($data);
        $this->loadAuthors($book);
        
        return $book;
    }

    public function findByIsbn(string $isbn): ?Book
    {
        $sql = "SELECT * FROM books WHERE isbn = :isbn";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['isbn' => $isbn]);
        
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        $book = $this->createBookFromData($data);
        $this->loadAuthors($book);
        
        return $book;
    }

    public function searchByTitle(string $title): array
    {
        $sql = "SELECT * FROM books WHERE title LIKE :title";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['title' => "%{$title}%"]);
        
        $books = [];
        while ($data = $stmt->fetch()) {
            $book = $this->createBookFromData($data);
            $this->loadAuthors($book);
            $books[] = $book;
        }
        
        return $books;
    }

    public function searchByAuthor(string $authorName): array
    {
        $sql = "SELECT DISTINCT b.* FROM books b
                INNER JOIN book_authors ba ON b.id = ba.book_id
                INNER JOIN authors a ON ba.author_id = a.id
                WHERE a.name LIKE :name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['name' => "%{$authorName}%"]);
        
        $books = [];
        while ($data = $stmt->fetch()) {
            $book = $this->createBookFromData($data);
            $this->loadAuthors($book);
            $books[] = $book;
        }
        
        return $books;
    }

    public function getAvailableCopies(int $bookId, int $branchId): int
    {
        $sql = "SELECT available_copies FROM book_inventory 
                WHERE book_id = :book_id AND branch_id = :branch_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['book_id' => $bookId, 'branch_id' => $branchId]);
        
        $result = $stmt->fetch();
        return $result ? (int)$result['available_copies'] : 0;
    }

    public function updateInventory(int $bookId, int $branchId, int $change): bool
    {
        $sql = "UPDATE book_inventory 
                SET available_copies = available_copies + :change 
                WHERE book_id = :book_id AND branch_id = :branch_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'change' => $change,
            'book_id' => $bookId,
            'branch_id' => $branchId
        ]);
    }

    public function getBranchesWithBook(int $bookId): array
    {
        $sql = "SELECT lb.*, bi.available_copies 
                FROM library_branches lb
                INNER JOIN book_inventory bi ON lb.id = bi.branch_id
                WHERE bi.book_id = :book_id AND bi.available_copies > 0
                ORDER BY lb.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['book_id' => $bookId]);
        
        return $stmt->fetchAll();
    }

    private function loadAuthors(Book $book): void
    {
        $sql = "SELECT a.* FROM authors a
                INNER JOIN book_authors ba ON a.id = ba.author_id
                WHERE ba.book_id = :book_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['book_id' => $book->getId()]);
        
        while ($data = $stmt->fetch()) {
            $author = new Author(
                $data['name'],
                $data['biography'],
                $data['nationality'],
                $data['birth_date'] ? new DateTime($data['birth_date']) : null,
                $data['death_date'] ? new DateTime($data['death_date']) : null,
                $data['primary_genre']
            );
            $author->setId((int)$data['id']);
            $book->addAuthor($author);
        }
    }

    private function createBookFromData(array $data): Book
    {
        $book = new Book(
            $data['isbn'],
            $data['title'],
            (int)$data['publication_year'],
            isset($data['category_id']) ? (int)$data['category_id'] : null
        );

        $book->setId((int)$data['id']);
        $book->setTotalCopies((int)$data['total_copies']);

        return $book;
    }
}