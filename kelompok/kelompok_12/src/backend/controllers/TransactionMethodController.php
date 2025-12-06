<?php
declare(strict_types=1);

namespace Controllers;

use Core\HttpException;
use Core\Request;
use Core\Response;
use Models\TransactionMethod;

final class TransactionMethodController
{
    public function __construct(private readonly TransactionMethod $methods = new TransactionMethod())
    {
    }

    public function index(Request $request, array $params = []): Response
    {
        return Response::json(['data' => $this->methods->all()]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $method = $this->methods->find((int) $params['id']);
        if ($method === null) {
            throw new HttpException('Metode pembayaran tidak ditemukan', 404);
        }

        return Response::json(['data' => $method]);
    }

    public function store(Request $request, array $params = []): Response
    {
        $payload = $this->validate($request->body());
        $created = $this->methods->create($payload);

        return Response::json(['message' => 'Metode pembayaran dibuat', 'data' => $created], 201);
    }

    public function update(Request $request, array $params = []): Response
    {
        $existing = $this->methods->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Metode pembayaran tidak ditemukan', 404);
        }

        $payload = $this->validate($request->body());
        $updated = $this->methods->update((int) $params['id'], $payload);

        return Response::json(['message' => 'Metode pembayaran diperbarui', 'data' => $updated]);
    }

    public function destroy(Request $request, array $params = []): Response
    {
        $existing = $this->methods->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Metode pembayaran tidak ditemukan', 404);
        }

        $this->methods->delete((int) $params['id']);

        return Response::json(['message' => 'Metode pembayaran dihapus']);
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
            'needs_proof' => filter_var($body['needs_proof'] ?? false, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            'is_active' => filter_var($body['is_active'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,
        ];
    }
}
