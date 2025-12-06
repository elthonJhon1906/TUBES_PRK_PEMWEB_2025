<?php
declare(strict_types=1);

namespace Controllers;

use Core\HttpException;
use Core\Request;
use Core\Response;
use Models\ProofType;

final class ProofTypeController
{
    public function __construct(private readonly ProofType $types = new ProofType())
    {
    }

    public function index(Request $request, array $params = []): Response
    {
        return Response::json(['data' => $this->types->all()]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $type = $this->types->find((int) $params['id']);
        if ($type === null) {
            throw new HttpException('Jenis bukti tidak ditemukan', 404);
        }

        return Response::json(['data' => $type]);
    }

    public function store(Request $request, array $params = []): Response
    {
        $payload = $this->validate($request->body());
        $created = $this->types->create($payload);

        return Response::json(['message' => 'Jenis bukti dibuat', 'data' => $created], 201);
    }

    public function update(Request $request, array $params = []): Response
    {
        $existing = $this->types->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Jenis bukti tidak ditemukan', 404);
        }

        $payload = $this->validate($request->body());
        $updated = $this->types->update((int) $params['id'], $payload);

        return Response::json(['message' => 'Jenis bukti diperbarui', 'data' => $updated]);
    }

    public function destroy(Request $request, array $params = []): Response
    {
        $existing = $this->types->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Jenis bukti tidak ditemukan', 404);
        }

        $this->types->delete((int) $params['id']);

        return Response::json(['message' => 'Jenis bukti dihapus']);
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
