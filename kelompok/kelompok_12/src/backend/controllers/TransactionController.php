<?php
declare(strict_types=1);

namespace Controllers;

use Core\HttpException;
use Core\Request;
use Core\Response;
use Models\ProofType;
use Models\Transaction;
use Models\TransactionMethod;
use Models\VerificationStatus;

final class TransactionController
{
    public function __construct(
        private readonly Transaction $transactions = new Transaction(),
        private readonly TransactionMethod $methods = new TransactionMethod(),
        private readonly ProofType $proofTypes = new ProofType(),
        private readonly VerificationStatus $verificationStatuses = new VerificationStatus()
    ) {
    }

    public function index(Request $request, array $params = []): Response
    {
        return Response::json(['data' => $this->transactions->all()]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $transaction = $this->transactions->find((int) $params['id']);
        if ($transaction === null) {
            throw new HttpException('Transaksi tidak ditemukan', 404);
        }

        return Response::json(['data' => $transaction]);
    }

    public function store(Request $request, array $params = []): Response
    {
        $payload = $this->validateCreate($request->body());

        $method = $this->methods->find($payload['transaction_method_id']);
        if ($method === null || (int) $method['is_active'] === 0) {
            throw new HttpException('Metode pembayaran tidak valid.', 422);
        }

        $proof = $this->proofTypes->find($payload['proof_type_id']);
        if ($proof === null || (int) $proof['is_active'] === 0) {
            throw new HttpException('Jenis bukti tidak valid.', 422);
        }

        $status = $this->verificationStatuses->find($payload['verification_status_id']);
        if ($status === null || (int) $status['is_active'] === 0) {
            throw new HttpException('Status verifikasi tidak valid.', 422);
        }

        $created = $this->transactions->create($payload);

        return Response::json(['message' => 'Transaksi dicatat', 'data' => $created], 201);
    }

    public function update(Request $request, array $params = []): Response
    {
        $existing = $this->transactions->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Transaksi tidak ditemukan', 404);
        }

        $payload = $this->validateUpdate($request->body());
        $status = $this->verificationStatuses->find($payload['verification_status_id']);
        if ($status === null || (int) $status['is_active'] === 0) {
            throw new HttpException('Status verifikasi tidak valid.', 422);
        }

        $updated = $this->transactions->update((int) $params['id'], $payload);

        return Response::json(['message' => 'Status verifikasi diperbarui', 'data' => $updated]);
    }

    private function validateCreate(array $body): array
    {
        $orderId = $body['order_id'] ?? null;
        $methodId = $body['transaction_method_id'] ?? null;
        $amountPaid = isset($body['amount_paid']) ? (float) $body['amount_paid'] : null;
        $changeAmount = isset($body['change_amount']) ? (float) $body['change_amount'] : 0.0;
        $proofTypeId = $body['proof_type_id'] ?? null;
        $proofImageUrl = trim((string) ($body['proof_image_url'] ?? ''));
        $verificationStatusId = $body['verification_status_id'] ?? null;
        $staffId = $body['staff_id'] ?? null;

        if (!is_numeric($orderId) || !is_numeric($methodId) || $amountPaid === null || !is_numeric($proofTypeId) || $proofImageUrl === '' || !is_numeric($verificationStatusId) || !is_numeric($staffId)) {
            throw new HttpException('Data transaksi tidak lengkap.', 422);
        }

        $transactionCode = isset($body['transaction_code']) ? trim((string) $body['transaction_code']) : null;
        if ($transactionCode !== null && $transactionCode !== '' && strlen($transactionCode) > 20) {
            $transactionCode = substr($transactionCode, 0, 20);
        }
        $transactionDate = isset($body['transaction_date']) ? $this->normalizeDateTime((string) $body['transaction_date']) : null;
        return [
            'order_id' => (int) $orderId,
            'transaction_method_id' => (int) $methodId,
            'amount_paid' => $amountPaid,
            'change_amount' => $changeAmount,
            'proof_type_id' => (int) $proofTypeId,
            'proof_image_url' => $proofImageUrl,
            'bank_name' => isset($body['bank_name']) ? trim((string) $body['bank_name']) : null,
            'account_name' => isset($body['account_name']) ? trim((string) $body['account_name']) : null,
            'reference_number' => isset($body['reference_number']) ? trim((string) $body['reference_number']) : null,
            'verification_status_id' => (int) $verificationStatusId,
            'verified_by' => isset($body['verified_by']) ? (int) $body['verified_by'] : null,
            'verified_at' => isset($body['verified_at']) ? $this->normalizeDateTime((string) $body['verified_at']) : null,
            'staff_id' => (int) $staffId,
            'notes' => isset($body['notes']) ? trim((string) $body['notes']) : null,
            'transaction_code' => $transactionCode !== '' ? $transactionCode : null,
            'transaction_date' => $transactionDate,
        ];
    }

    private function validateUpdate(array $body): array
    {
        $verificationStatusId = $body['verification_status_id'] ?? null;

        if (!is_numeric($verificationStatusId)) {
            throw new HttpException('verification_status_id wajib diisi.', 422);
        }

        return [
            'verification_status_id' => (int) $verificationStatusId,
            'verified_by' => isset($body['verified_by']) ? (int) $body['verified_by'] : null,
            'verified_at' => isset($body['verified_at'])
                ? $this->normalizeDateTime((string) $body['verified_at'])
                : date('Y-m-d H:i:s'),
            'notes' => isset($body['notes']) ? trim((string) $body['notes']) : null,
        ];
    }

    private function normalizeDateTime(string $value): string
    {
        if ($value === '') {
            return date('Y-m-d H:i:s');
        }

        $date = date_create($value);

        if ($date === false) {
            throw new HttpException('Format tanggal tidak valid.', 422);
        }

        return $date->format('Y-m-d H:i:s');
    }
}
