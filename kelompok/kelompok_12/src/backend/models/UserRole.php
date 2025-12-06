<?php
declare(strict_types=1);

namespace Models;

use Database;
use PDO;

final class UserRole
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT id, code, name, description, level, is_active, created_at FROM user_roles ORDER BY level ASC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, code, name, description, level, is_active, created_at FROM user_roles WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $role = $stmt->fetch();
        return $role ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, code, name, description, level, is_active, created_at FROM user_roles WHERE code = :code');
        $stmt->execute(['code' => $code]);
        $role = $stmt->fetch();
        return $role ?: null;
    }

    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO user_roles (code, name, description, level, is_active) VALUES (:code, :name, :description, :level, :is_active)'
        );
        $stmt->execute([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'level' => $data['level'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $this->find((int) $this->pdo->lastInsertId());
    }

    public function update(int $id, array $data): ?array
    {
        $stmt = $this->pdo->prepare(
            'UPDATE user_roles SET code = :code, name = :name, description = :description, level = :level, is_active = :is_active WHERE id = :id'
        );
        $stmt->execute([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'level' => $data['level'],
            'is_active' => $data['is_active'] ?? true,
            'id' => $id,
        ]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM user_roles WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
