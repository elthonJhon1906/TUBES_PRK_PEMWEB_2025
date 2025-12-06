<?php
declare(strict_types=1);

namespace Models;

use Database;
use PDO;

final class Expense
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT e.id,
                    e.expense_code,
                    e.expense_date,
                    e.category_id,
                    ec.name AS category_name,
                    e.product_id,
                    p.name AS product_name,
                    e.product_batch_id,
                    pb.batch_code,
                    e.description,
                    e.amount,
                    e.transaction_method_id,
                    em.name AS transaction_method_name,
                    e.created_by,
                    e.notes,
                    e.created_at
             FROM expenses e
             JOIN expense_categories ec ON ec.id = e.category_id
             JOIN expense_methods em ON em.id = e.transaction_method_id
             LEFT JOIN products p ON p.id = e.product_id
             LEFT JOIN product_batches pb ON pb.id = e.product_batch_id
             ORDER BY e.expense_date DESC, e.created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.id,
                    e.expense_code,
                    e.expense_date,
                    e.category_id,
                    ec.name AS category_name,
                    e.product_id,
                    p.name AS product_name,
                    e.product_batch_id,
                    pb.batch_code,
                    e.description,
                    e.amount,
                    e.transaction_method_id,
                    em.name AS transaction_method_name,
                    e.created_by,
                    e.notes,
                    e.created_at
             FROM expenses e
             JOIN expense_categories ec ON ec.id = e.category_id
             JOIN expense_methods em ON em.id = e.transaction_method_id
             LEFT JOIN products p ON p.id = e.product_id
             LEFT JOIN product_batches pb ON pb.id = e.product_batch_id
             WHERE e.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $payload): array
    {
        $code = $payload['expense_code'] ?? $this->generateCode();

        $stmt = $this->pdo->prepare(
            'INSERT INTO expenses (expense_code, expense_date, category_id, product_id, product_batch_id, description, amount, transaction_method_id, created_by, notes)
             VALUES (:expense_code, :expense_date, :category_id, :product_id, :product_batch_id, :description, :amount, :transaction_method_id, :created_by, :notes)'
        );

        $stmt->execute([
            'expense_code' => $code,
            'expense_date' => $payload['expense_date'],
            'category_id' => $payload['category_id'],
            'product_id' => $payload['product_id'],
            'product_batch_id' => $payload['product_batch_id'],
            'description' => $payload['description'],
            'amount' => $payload['amount'],
            'transaction_method_id' => $payload['transaction_method_id'],
            'created_by' => $payload['created_by'],
            'notes' => $payload['notes'],
        ]);

        return $this->find((int) $this->pdo->lastInsertId());
    }

    public function update(int $id, array $payload): ?array
    {
        $stmt = $this->pdo->prepare(
            'UPDATE expenses
             SET expense_date = :expense_date,
                 category_id = :category_id,
                 product_id = :product_id,
                 product_batch_id = :product_batch_id,
                 description = :description,
                 amount = :amount,
                 transaction_method_id = :transaction_method_id,
                 notes = :notes
             WHERE id = :id'
        );

        $stmt->execute([
            'expense_date' => $payload['expense_date'],
            'category_id' => $payload['category_id'],
            'product_id' => $payload['product_id'],
            'product_batch_id' => $payload['product_batch_id'],
            'description' => $payload['description'],
            'amount' => $payload['amount'],
            'transaction_method_id' => $payload['transaction_method_id'],
            'notes' => $payload['notes'],
            'id' => $id,
        ]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM expenses WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function generateCode(): string
    {
        return substr('EXP-' . date('ymdHis') . '-' . random_int(100, 999), 0, 20);
    }
}
