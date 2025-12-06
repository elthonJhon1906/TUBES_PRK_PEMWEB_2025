<?php
declare(strict_types=1);

namespace Controllers;

use Core\HttpException;
use Core\Request;
use Core\Response;
use Models\Expense;
use Models\ExpenseCategory;
use Models\ExpenseMethod;
use Models\Product;
use Models\ProductBatch;

final class ExpenseController
{
    public function __construct(
        private readonly Expense $expenses = new Expense(),
        private readonly ExpenseCategory $categories = new ExpenseCategory(),
        private readonly ExpenseMethod $methods = new ExpenseMethod(),
        private readonly Product $products = new Product(),
        private readonly ProductBatch $batches = new ProductBatch()
    ) {
    }

    public function index(Request $request, array $params = []): Response
    {
        return Response::json(['data' => $this->expenses->all()]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $expense = $this->expenses->find((int) $params['id']);
        if ($expense === null) {
            throw new HttpException('Pengeluaran tidak ditemukan', 404);
        }

        return Response::json(['data' => $expense]);
    }

    public function store(Request $request, array $params = []): Response
    {
        $payload = $this->validate($request->body(), true);
        $created = $this->expenses->create($payload);

        return Response::json(['message' => 'Pengeluaran dicatat', 'data' => $created], 201);
    }

    public function update(Request $request, array $params = []): Response
    {
        $existing = $this->expenses->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Pengeluaran tidak ditemukan', 404);
        }

        $payload = $this->validate($request->body(), false);
        $updated = $this->expenses->update((int) $params['id'], $payload);

        return Response::json(['message' => 'Pengeluaran diperbarui', 'data' => $updated]);
    }

    public function destroy(Request $request, array $params = []): Response
    {
        $existing = $this->expenses->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Pengeluaran tidak ditemukan', 404);
        }

        $this->expenses->delete((int) $params['id']);

        return Response::json(['message' => 'Pengeluaran dihapus']);
    }

    private function validate(array $body, bool $isCreate): array
    {
        $expenseDate = isset($body['expense_date']) ? $this->normalizeDate((string) $body['expense_date']) : null;
        $categoryId = $body['category_id'] ?? null;
        $methodId = $body['transaction_method_id'] ?? null;
        $amount = isset($body['amount']) ? (float) $body['amount'] : null;
        $description = isset($body['description']) ? trim((string) $body['description']) : '';

        if ($expenseDate === null || !is_numeric($categoryId) || !is_numeric($methodId) || $amount === null || $description === '') {
            throw new HttpException('Data pengeluaran tidak lengkap.', 422);
        }

        $category = $this->categories->find((int) $categoryId);
        if ($category === null || (int) $category['is_active'] === 0) {
            throw new HttpException('Kategori pengeluaran tidak valid.', 422);
        }

        $method = $this->methods->find((int) $methodId);
        if ($method === null || (int) $method['is_active'] === 0) {
            throw new HttpException('Metode pengeluaran tidak valid.', 422);
        }

        $productId = $body['product_id'] ?? null;
        if ($productId !== null && $productId !== '' && $this->products->find((int) $productId) === null) {
            throw new HttpException('Produk tidak ditemukan.', 422);
        }

        $productBatchId = $body['product_batch_id'] ?? null;
        if ($productBatchId !== null && $productBatchId !== '' && $this->batches->find((int) $productBatchId) === null) {
            throw new HttpException('Batch produk tidak ditemukan.', 422);
        }

        $payload = [
            'expense_date' => $expenseDate,
            'category_id' => (int) $categoryId,
            'product_id' => $productId !== null && $productId !== '' ? (int) $productId : null,
            'product_batch_id' => $productBatchId !== null && $productBatchId !== '' ? (int) $productBatchId : null,
            'description' => $description,
            'amount' => $amount,
            'transaction_method_id' => (int) $methodId,
            'notes' => isset($body['notes']) ? trim((string) $body['notes']) : null,
        ];

        if ($isCreate) {
            $createdBy = $body['created_by'] ?? null;
            if (!is_numeric($createdBy)) {
                throw new HttpException('created_by wajib diisi.', 422);
            }
            $payload['created_by'] = (int) $createdBy;
            $expenseCode = isset($body['expense_code']) ? trim((string) $body['expense_code']) : null;
            if ($expenseCode !== null && $expenseCode !== '' && strlen($expenseCode) > 20) {
                $expenseCode = substr($expenseCode, 0, 20);
            }
            $payload['expense_code'] = $expenseCode !== '' ? $expenseCode : null;
        }

        return $payload;
    }

    private function normalizeDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $date = date_create($value);
        if ($date === false) {
            throw new HttpException('Format tanggal tidak valid.', 422);
        }

        return $date->format('Y-m-d');
    }
}
