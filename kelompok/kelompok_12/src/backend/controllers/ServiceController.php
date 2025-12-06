<?php
declare(strict_types=1);

namespace Controllers;

use Core\HttpException;
use Core\Request;
use Core\Response;
use Models\Service;
use Models\ServiceCategory;

final class ServiceController
{
    public function __construct(
        private readonly Service $services = new Service(),
        private readonly ServiceCategory $categories = new ServiceCategory()
    ) {
    }

    public function index(Request $request, array $params = []): Response
    {
        return Response::json(['data' => $this->services->all()]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $service = $this->services->find((int) $params['id']);
        if ($service === null) {
            throw new HttpException('Layanan tidak ditemukan', 404);
        }

        return Response::json(['data' => $service]);
    }

    public function store(Request $request, array $params = []): Response
    {
        $payload = $this->validate($request->body());
        $category = $this->categories->find($payload['category_id']);
        if ($category === null || (int) $category['is_active'] === 0) {
            throw new HttpException('Kategori layanan tidak tersedia.', 422);
        }

        $created = $this->services->create($payload);

        return Response::json(['message' => 'Layanan dibuat', 'data' => $created], 201);
    }

    public function update(Request $request, array $params = []): Response
    {
        $existing = $this->services->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Layanan tidak ditemukan', 404);
        }

        $payload = $this->validate($request->body());

        $category = $this->categories->find($payload['category_id']);
        if ($category === null || (int) $category['is_active'] === 0) {
            throw new HttpException('Kategori layanan tidak tersedia.', 422);
        }

        $updated = $this->services->update((int) $params['id'], $payload);

        return Response::json(['message' => 'Layanan diperbarui', 'data' => $updated]);
    }

    public function destroy(Request $request, array $params = []): Response
    {
        $existing = $this->services->find((int) $params['id']);
        if ($existing === null) {
            throw new HttpException('Layanan tidak ditemukan', 404);
        }

        $this->services->delete((int) $params['id']);

        return Response::json(['message' => 'Layanan dihapus']);
    }

    private function validate(array $body): array
    {
        $code = trim((string) ($body['code'] ?? ''));
        $name = trim((string) ($body['name'] ?? ''));
        $categoryId = $body['category_id'] ?? null;
        $basePrice = isset($body['base_price']) ? (float) $body['base_price'] : null;
        $unit = trim((string) ($body['unit'] ?? ''));
        $estimatedMinutes = $body['estimated_minutes'] ?? null;

        if ($code === '' || $name === '' || !is_numeric($categoryId) || $unit === '' || $basePrice === null || $basePrice < 0) {
            throw new HttpException('code, name, category_id, unit, dan base_price wajib diisi.', 422);
        }

        if ($estimatedMinutes !== null && !is_numeric($estimatedMinutes)) {
            throw new HttpException('estimated_minutes harus berupa angka atau dikosongkan.', 422);
        }

        return [
            'code' => $code,
            'name' => $name,
            'category_id' => (int) $categoryId,
            'description' => isset($body['description']) ? trim((string) $body['description']) : null,
            'base_price' => $basePrice,
            'unit' => $unit,
            'estimated_minutes' => $estimatedMinutes !== null ? (int) $estimatedMinutes : null,
            'is_active' => filter_var($body['is_active'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,
        ];
    }
}
