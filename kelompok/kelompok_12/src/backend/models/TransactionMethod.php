<?php
declare(strict_types=1);

namespace Models;

use Database;
use PDO;

final class TransactionMethod
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, code, name, description, needs_proof, is_active, created_at FROM transaction_methods ORDER BY created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, code, name, description, needs_proof, is_active, created_at FROM transaction_methods WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $payload): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO transaction_methods (code, name, description, needs_proof, is_active)
             VALUES (:code, :name, :description, :needs_proof, :is_active)'
        );
        $stmt->execute([
            'code' => $payload['code'],
            'name' => $payload['name'],
            'description' => $payload['description'],
            'needs_proof' => $payload['needs_proof'],
            'is_active' => $payload['is_active'],
        ]);

        return $this->find((int) $this->pdo->lastInsertId());
    }

    public function update(int $id, array $payload): ?array
    {
        $stmt = $this->pdo->prepare(
            'UPDATE transaction_methods
             SET code = :code, name = :name, description = :description, needs_proof = :needs_proof, is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute([
            'code' => $payload['code'],
            'name' => $payload['name'],
            'description' => $payload['description'],
            'needs_proof' => $payload['needs_proof'],
            'is_active' => $payload['is_active'],
            'id' => $id,
        ]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM transaction_methods WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
