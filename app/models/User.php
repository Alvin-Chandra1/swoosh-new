<?php
declare(strict_types=1);

final class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $statement->execute([strtolower(trim($email))]);
        return $statement->fetch() ?: null;
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT id, name, email, role, phone, created_at FROM users WHERE id = ?');
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO users (name, email, password, role, phone) VALUES (:name, :email, :password, :role, :phone)'
        );
        $statement->execute([
            ':name' => trim($data['name']),
            ':email' => strtolower(trim($data['email'])),
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role' => $data['role'],
            ':phone' => trim($data['phone'] ?? ''),
        ]);
        return (int) $this->db->lastInsertId();
    }
}