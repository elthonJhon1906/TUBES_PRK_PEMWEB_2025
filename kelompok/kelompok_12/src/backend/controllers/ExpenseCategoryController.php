<?php
declare(strict_types=1);

namespace Controllers;

use Core\HttpException;
use Core\Request;
use Core\Response;
use Models\ExpenseCategory;

final class ExpenseCategoryController
{
    public function __construct(private readonly ExpenseCategory $categories = new ExpenseCategory())
    {
    }

    public function index(Request $request, array $params = []): Response
    {
        return Response::json(['data' => $this->categories->all()]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $category = $this->categories->find((int) $params['id']);
        if ($category === null) {
            throw new HttpException('Kategori pengeluaran tidak ditemukan', 404);
        }

        return Response::json(['data' => $category]);
    }

    public function store(Request $request, array $params = []): Response
    {
        $payload = $this->validate($request->body());
        $created = $this->categories->create($payload);

        return Response::json(['message' => 'Kategori pengeluaran dibuat', 'data' => $created], 201);
    }

    public function update(Request $request, array $params = []): Response
    {
        $existing = $this->categories->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Kategori pengeluaran tidak ditemukan', 404);
        }

        $payload = $this->validate($request->body());
        $updated = $this->categories->update((int) $params['id'], $payload);

        return Response::json(['message' => 'Kategori pengeluaran diperbarui', 'data' => $updated]);
    }

    public function destroy(Request $request, array $params = []): Response
    {
        $existing = $this->categories->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Kategori pengeluaran tidak ditemukan', 404);
        }

        $this->categories->delete((int) $params['id']);

        return Response::json(['message' => 'Kategori pengeluaran dihapus']);
    }

    private function validate(array $body): array
    {
        $code = trim((string) ($body['code'] ?? ''));
        $name = trim((string) ($body['name'] ?? ''));

        if ($code === '' || $name === '') {
            throw new HttpException('code dan name wajib diisi.', 422);
        }

        return [
            'code' => $code,
            'name' => $name,
            'description' => isset($body['description']) ? trim((string) $body['description']) : null,
            'is_active' => filter_var($body['is_active'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,
        ];
    }
}
