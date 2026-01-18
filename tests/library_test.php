<?php

require_once __DIR__ . '/../vendor/autoload.php';

use LibraryManagement\Services\LibraryService;
use LibraryManagement\Repositories\MemberRepository;
use LibraryManagement\Repositories\BookRepository;
use LibraryManagement\Repositories\DatabaseConnection;

echo "LIBRARY MANAGEMENT SYSTEM - TESTS\n\n";

$service    = new LibraryService();
$memberRepo = new MemberRepository();
$bookRepo   = new BookRepository();

/* TEST 1 */
echo "TEST 1: Student borrows a book\n";
try {
    $member = $memberRepo->findByEmail('alice.j@techcity.edu');
    $book   = $bookRepo->findByIsbn('978-0132350884');

    $record = $service->borrowBook($member->getId(), $book->getId(), 1);

    echo "Borrowed: {$book->getTitle()}\n";
    echo "Due: {$record->getDueDate()->format('Y-m-d')}\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
echo "\n";

/* TEST 2 */
echo "TEST 2: Faculty borrows multiple books\n";
try {
    $member = $memberRepo->findByEmail('carol.w@techcity.edu');
    $isbns  = [
        '978-0134685991',
        '978-0132350882',
        '978-0451524935'
    ];

    foreach ($isbns as $isbn) {
        try {
            $book = $bookRepo->findByIsbn($isbn);
            $service->borrowBook($member->getId(), $book->getId(), 1);
            echo "Borrowed: {$book->getTitle()}\n";
        } catch (Exception $e) {
            echo "Failed: {$e->getMessage()}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
echo "\n";

/* TEST 3 */
echo "TEST 3: Late return\n";
try {
    $member = $memberRepo->findByEmail('bob.s@techcity.edu');
    $book   = $bookRepo->findByIsbn('978-0553293357');

    $record = $service->borrowBook($member->getId(), $book->getId(), 1);

    $past = new DateTime('-10 days');
    $db   = DatabaseConnection::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE borrow_records SET due_date = :d WHERE id = :i");
    $stmt->execute(['d' => $past->format('Y-m-d'), 'i' => $record->getId()]);

    $result = $service->returnBook($member->getId(), $book->getId());

    echo "Returned\n";
    echo "Late fee: {$result['late_fee']}\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
echo "\n";

/* TEST 4 */
echo "TEST 4: Book unavailable\n";
try {
    $member = $memberRepo->findByEmail('alice.j@techcity.edu');
    $book   = $bookRepo->findByIsbn('978-0132350884');

    $service->borrowBook($member->getId(), $book->getId(), 5);
    echo "Unexpected success\n";
} catch (Exception $e) {
    echo "Expected failure: {$e->getMessage()}\n";
}
echo "\n";

/* TEST 5 */
echo "TEST 5: Borrow limit exceeded\n";
try {
    $member = $memberRepo->findByEmail('bob.s@techcity.edu');
    $books  = array_slice($bookRepo->searchByTitle(''), 0, 4);

    foreach ($books as $book) {
        $service->borrowBook($member->getId(), $book->getId(), 1);
        echo "Borrowed: {$book->getTitle()}\n";
    }
} catch (Exception $e) {
    echo "Limit enforced: {$e->getMessage()}\n";
}
echo "\n";

/* TEST 6 */
echo "TEST 6: Book search\n";
try {
    $results = $service->searchBooks('Clean', 'title');
    foreach ($results as $book) {
        echo "{$book->getTitle()} ({$book->getIsbn()})\n";
    }
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
echo "\n";

/* TEST 7 */
echo "TEST 7: Member history\n";
try {
    $member  = $memberRepo->findByEmail('carol.w@techcity.edu');
    $history = $service->getMemberHistory($member->getId(), 5);

    foreach ($history as $record) {
        echo $record->getBorrowDate()->format('Y-m-d') . " -> ";
        echo $record->getDueDate()->format('Y-m-d') . "\n";
    }
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
echo "\n";

/* TEST 8 */
echo "TEST 8: Overdue books\n";
try {
    $overdue = $service->getOverdueBooks();
    foreach ($overdue as $record) {
        echo "{$record['title']} - {$record['full_name']}\n";
    }
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
echo "\n";

/* TEST 9 */
echo "TEST 9: Renewal\n";
try {
    $member = $memberRepo->findByEmail('alice.j@techcity.edu');
    $book   = $bookRepo->findByIsbn('978-0132350884');

    $record = $service->renewBook($member->getId(), $book->getId());
    echo "New due date: {$record->getDueDate()->format('Y-m-d')}\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
echo "\n";

/* TEST 10 */
echo "TEST 10: Expired membership\n";
try {
    $member = $memberRepo->findByEmail('eve.d@techcity.edu');
    $book   = $bookRepo->findByIsbn('978-0451524935');

    $service->borrowBook($member->getId(), $book->getId(), 1);
    echo "Unexpected success\n";
} catch (Exception $e) {
    echo "Access denied: {$e->getMessage()}\n";
}

echo "\nALL TESTS COMPLETED\n";
