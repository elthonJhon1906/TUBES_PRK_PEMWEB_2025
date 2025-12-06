<?php
declare(strict_types=1);

namespace Models;

use Database;
use PDO;

final class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, username, email, full_name, phone, role_id, is_active, created_at FROM users ORDER BY created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, full_name, phone, role_id, is_active, created_at FROM users WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public function create(array $payload): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, email, password, full_name, phone, role_id, is_active)
             VALUES (:username, :email, :password, :full_name, :phone, :role_id, :is_active)'
        );

        $stmt->execute([
            'username' => $payload['username'],
            'email' => $payload['email'],
            'password' => password_hash($payload['password'], PASSWORD_BCRYPT),
            'full_name' => $payload['full_name'],
            'phone' => $payload['phone'] ?? null,
            'role_id' => $payload['role_id'],
            'is_active' => $payload['is_active'] ?? true,
        ]);

        return $this->find((int) $this->pdo->lastInsertId());
    }

    public function update(int $id, array $payload): ?array
    {
        $fields = [
            'username' => $payload['username'],
            'email' => $payload['email'],
            'full_name' => $payload['full_name'],
            'phone' => $payload['phone'] ?? null,
            'role_id' => $payload['role_id'],
            'is_active' => $payload['is_active'] ?? true,
            'id' => $id,
        ];

        $sql = 'UPDATE users SET username = :username, email = :email, full_name = :full_name, phone = :phone, role_id = :role_id, is_active = :is_active';

        if (!empty($payload['password'])) {
            $fields['password'] = password_hash($payload['password'], PASSWORD_BCRYPT);
            $sql .= ', password = :password';
        }

        $sql .= ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($fields);

        return $this->find($id);
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, password, full_name, phone, role_id, is_active FROM users WHERE username = :username'
        );

        $stmt->execute(['username' => $username]);
        $record = $stmt->fetch();

        return $record ?: null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
