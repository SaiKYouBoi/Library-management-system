<?php
// src/Repositories/MemberRepository.php

namespace LibraryManagement\Repositories;

use LibraryManagement\Models\Member;
use LibraryManagement\Models\StudentMember;
use LibraryManagement\Models\FacultyMember;
use PDO;
use DateTime;

class MemberRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function save(Member $member): int
    {
        $sql = "INSERT INTO members (member_type, full_name, email, phone, membership_start, membership_end, total_borrowed) 
                VALUES (:type, :name, :email, :phone, :start, :end, :total)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'type' => $member->getMemberType(),
            'name' => $member->getFullName(),
            'email' => $member->getEmail(),
            'phone' => $member->getPhone(),
            'start' => $member->getMembershipStart()->format('Y-m-d'),
            'end' => $member->getMembershipEnd()->format('Y-m-d'),
            'total' => $member->getTotalBorrowed()
        ]);

        $memberId = (int)$this->db->lastInsertId();
        $member->setId($memberId);
        return $memberId;
    }

    public function findById(int $id): ?Member
    {
        $sql = "SELECT * FROM members WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return $this->createMemberFromData($data);
    }

    public function findByEmail(string $email): ?Member
    {
        $sql = "SELECT * FROM members WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return $this->createMemberFromData($data);
    }

    public function update(Member $member): bool
    {
        $sql = "UPDATE members SET full_name = :name, email = :email, phone = :phone, 
                membership_end = :end, total_borrowed = :total WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name' => $member->getFullName(),
            'email' => $member->getEmail(),
            'phone' => $member->getPhone(),
            'end' => $member->getMembershipEnd()->format('Y-m-d'),
            'total' => $member->getTotalBorrowed(),
            'id' => $member->getId()
        ]);
    }

    public function getCurrentBorrowedCount(int $memberId): int
    {
        $sql = "SELECT COUNT(*) as count FROM borrow_records 
                WHERE member_id = :id AND return_date IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $memberId]);
        
        return (int)$stmt->fetch()['count'];
    }

    public function getTotalUnpaidFees(int $memberId): float
    {
        $sql = "SELECT SUM(late_fee) as total FROM borrow_records 
                WHERE member_id = :id AND return_date IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $memberId]);
        
        $result = $stmt->fetch();
        return (float)($result['total'] ?? 0.00);
    }

    private function createMemberFromData(array $data): Member
    {
        $memberType = $data['member_type'];
        
        if ($memberType === 'Student') {
            $member = new StudentMember(
                $data['full_name'],
                $data['email'],
                $data['phone'],
                new DateTime($data['membership_start']),
                new DateTime($data['membership_end'])
            );
        } else {
            $member = new FacultyMember(
                $data['full_name'],
                $data['email'],
                $data['phone'],
                new DateTime($data['membership_start']),
                new DateTime($data['membership_end'])
            );
        }

        $member->setId((int)$data['id']);
        $member->setTotalBorrowed((int)$data['total_borrowed']);

        return $member;
    }
}