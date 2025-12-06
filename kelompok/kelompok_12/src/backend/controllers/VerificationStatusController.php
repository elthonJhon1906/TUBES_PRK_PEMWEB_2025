<?php
declare(strict_types=1);

namespace Controllers;

use Core\HttpException;
use Core\Request;
use Core\Response;
use Models\VerificationStatus;

final class VerificationStatusController
{
    public function __construct(private readonly VerificationStatus $statuses = new VerificationStatus())
    {
    }

    public function index(Request $request, array $params = []): Response
    {
        return Response::json(['data' => $this->statuses->all()]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $status = $this->statuses->find((int) $params['id']);
        if ($status === null) {
            throw new HttpException('Status verifikasi tidak ditemukan', 404);
        }

        return Response::json(['data' => $status]);
    }

    public function store(Request $request, array $params = []): Response
    {
        $payload = $this->validate($request->body());
        $created = $this->statuses->create($payload);

        return Response::json(['message' => 'Status verifikasi dibuat', 'data' => $created], 201);
    }

    public function update(Request $request, array $params = []): Response
    {
        $existing = $this->statuses->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Status verifikasi tidak ditemukan', 404);
        }

        $payload = $this->validate($request->body());
        $updated = $this->statuses->update((int) $params['id'], $payload);

        return Response::json(['message' => 'Status verifikasi diperbarui', 'data' => $updated]);
    }

    public function destroy(Request $request, array $params = []): Response
    {
        $existing = $this->statuses->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Status verifikasi tidak ditemukan', 404);
        }

        $this->statuses->delete((int) $params['id']);

        return Response::json(['message' => 'Status verifikasi dihapus']);
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
