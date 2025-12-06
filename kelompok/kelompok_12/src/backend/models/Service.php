<?php
declare(strict_types=1);

namespace Models;

use Database;
use PDO;

final class Service
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT s.id,
                    s.code,
                    s.name,
                    s.category_id,
                    c.name AS category_name,
                    c.code AS category_code,
                    s.description,
                    s.base_price,
                    s.unit,
                    s.estimated_minutes,
                    s.is_active,
                    s.created_at,
                    s.updated_at
             FROM services s
             JOIN service_categories c ON c.id = s.category_id
             ORDER BY s.created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.id,
                    s.code,
                    s.name,
                    s.category_id,
                    c.name AS category_name,
                    c.code AS category_code,
                    s.description,
                    s.base_price,
                    s.unit,
                    s.estimated_minutes,
                    s.is_active,
                    s.created_at,
                    s.updated_at
             FROM services s
             JOIN service_categories c ON c.id = s.category_id
             WHERE s.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $payload): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO services (code, name, category_id, description, base_price, unit, estimated_minutes, is_active)
             VALUES (:code, :name, :category_id, :description, :base_price, :unit, :estimated_minutes, :is_active)'
        );

        $stmt->execute([
            'code' => $payload['code'],
            'name' => $payload['name'],
            'category_id' => $payload['category_id'],
            'description' => $payload['description'],
            'base_price' => $payload['base_price'],
            'unit' => $payload['unit'],
            'estimated_minutes' => $payload['estimated_minutes'],
            'is_active' => $payload['is_active'],
        ]);

        return $this->find((int) $this->pdo->lastInsertId());
    }

    public function update(int $id, array $payload): ?array
    {
        $stmt = $this->pdo->prepare(
            'UPDATE services
             SET code = :code,
                 name = :name,
                 category_id = :category_id,
                 description = :description,
                 base_price = :base_price,
                 unit = :unit,
                 estimated_minutes = :estimated_minutes,
                 is_active = :is_active,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'code' => $payload['code'],
            'name' => $payload['name'],
            'category_id' => $payload['category_id'],
            'description' => $payload['description'],
            'base_price' => $payload['base_price'],
            'unit' => $payload['unit'],
            'estimated_minutes' => $payload['estimated_minutes'],
            'is_active' => $payload['is_active'],
            'id' => $id,
        ]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM services WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
