<?php
declare(strict_types=1);

namespace Models;

use Database;
use PDO;

final class Transaction
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT t.id,
                    t.transaction_code,
                    t.order_id,
                    t.transaction_date,
                    t.transaction_method_id,
                    tm.name AS transaction_method_name,
                    t.amount_paid,
                    t.change_amount,
                    t.proof_type_id,
                    pt.name AS proof_type_name,
                    t.proof_image_url,
                    t.bank_name,
                    t.account_name,
                    t.reference_number,
                    t.verification_status_id,
                    vs.name AS verification_status_name,
                    t.verified_by,
                    t.verified_at,
                    t.staff_id,
                    t.notes,
                    t.created_at
             FROM transactions t
             JOIN transaction_methods tm ON tm.id = t.transaction_method_id
             JOIN proof_types pt ON pt.id = t.proof_type_id
             JOIN verification_statuses vs ON vs.id = t.verification_status_id
             ORDER BY t.created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.id,
                    t.transaction_code,
                    t.order_id,
                    t.transaction_date,
                    t.transaction_method_id,
                    tm.name AS transaction_method_name,
                    t.amount_paid,
                    t.change_amount,
                    t.proof_type_id,
                    pt.name AS proof_type_name,
                    t.proof_image_url,
                    t.bank_name,
                    t.account_name,
                    t.reference_number,
                    t.verification_status_id,
                    vs.name AS verification_status_name,
                    t.verified_by,
                    t.verified_at,
                    t.staff_id,
                    t.notes,
                    t.created_at
             FROM transactions t
             JOIN transaction_methods tm ON tm.id = t.transaction_method_id
             JOIN proof_types pt ON pt.id = t.proof_type_id
             JOIN verification_statuses vs ON vs.id = t.verification_status_id
             WHERE t.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $payload): array
    {
        $code = $payload['transaction_code'] ?? $this->generateCode();

        $stmt = $this->pdo->prepare(
            'INSERT INTO transactions (transaction_code, order_id, transaction_date, transaction_method_id, amount_paid, change_amount, proof_type_id, proof_image_url, bank_name, account_name, reference_number, verification_status_id, verified_by, verified_at, staff_id, notes)
             VALUES (:transaction_code, :order_id, :transaction_date, :transaction_method_id, :amount_paid, :change_amount, :proof_type_id, :proof_image_url, :bank_name, :account_name, :reference_number, :verification_status_id, :verified_by, :verified_at, :staff_id, :notes)'
        );

        $stmt->execute([
            'transaction_code' => $code,
            'order_id' => $payload['order_id'],
            'transaction_date' => $payload['transaction_date'] ?? date('Y-m-d H:i:s'),
            'transaction_method_id' => $payload['transaction_method_id'],
            'amount_paid' => $payload['amount_paid'],
            'change_amount' => $payload['change_amount'],
            'proof_type_id' => $payload['proof_type_id'],
            'proof_image_url' => $payload['proof_image_url'],
            'bank_name' => $payload['bank_name'],
            'account_name' => $payload['account_name'],
            'reference_number' => $payload['reference_number'],
            'verification_status_id' => $payload['verification_status_id'],
            'verified_by' => $payload['verified_by'],
            'verified_at' => $payload['verified_at'],
            'staff_id' => $payload['staff_id'],
            'notes' => $payload['notes'],
        ]);

        return $this->find((int) $this->pdo->lastInsertId());
    }

    public function update(int $id, array $payload): ?array
    {
        $stmt = $this->pdo->prepare(
            'UPDATE transactions
             SET verification_status_id = :verification_status_id,
                 verified_by = :verified_by,
                 verified_at = :verified_at,
                 notes = :notes
             WHERE id = :id'
        );

        $stmt->execute([
            'verification_status_id' => $payload['verification_status_id'],
            'verified_by' => $payload['verified_by'],
            'verified_at' => $payload['verified_at'],
            'notes' => $payload['notes'],
            'id' => $id,
        ]);

        return $this->find($id);
    }

    private function generateCode(): string
    {
        return 'TRX-' . date('YmdHis') . '-' . random_int(100, 999);
    }
}
