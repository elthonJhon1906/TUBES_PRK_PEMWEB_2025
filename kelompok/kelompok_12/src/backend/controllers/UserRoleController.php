<?php
declare(strict_types=1);

namespace Controllers;

use Bootstrap\RoleBootstrapper;
use Core\HttpException;
use Core\Request;
use Core\Response;
use Models\UserRole;

final class UserRoleController
{
    public function __construct(private readonly UserRole $roles = new UserRole())
    {
    }

    public function index(Request $request, array $params = []): Response
    {
        return Response::json(['data' => $this->roles->all()]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $role = $this->roles->find((int) $params['id']);

        if ($role === null) {
            throw new HttpException('Role not found', 404);
        }

        return Response::json(['data' => $role]);
    }

    public function store(Request $request, array $params = []): Response
    {
        $payload = $this->validate($request->body());
        $created = $this->roles->create($payload);

        return Response::json(['message' => 'Role created', 'data' => $created], 201);
    }

    public function update(Request $request, array $params = []): Response
    {
        $role = $this->roles->find((int) $params['id']);
        if ($role === null) {
            throw new HttpException('Role not found', 404);
        }

        $incomingCode = isset($request->body()['code']) ? trim((string) $request->body()['code']) : $role['code'];
        if (in_array($role['code'], RoleBootstrapper::protectedCodes(), true) &&
            $incomingCode !== $role['code']) {
            throw new HttpException('Cannot change code for protected role.', 422);
        }

        $payload = $this->validate($request->body());
        $updated = $this->roles->update((int) $params['id'], $payload);

        return Response::json(['message' => 'Role updated', 'data' => $updated]);
    }

    public function destroy(Request $request, array $params = []): Response
    {
        $role = $this->roles->find((int) $params['id']);
        if ($role === null) {
            throw new HttpException('Role not found', 404);
        }

        if (in_array($role['code'], RoleBootstrapper::protectedCodes(), true)) {
            throw new HttpException('Cannot delete protected role.', 422);
        }

        $this->roles->delete((int) $params['id']);

        return Response::json(['message' => 'Role deleted']);
    }

    private function validate(array $payload): array
    {
        $code = trim((string) ($payload['code'] ?? ''));
        $name = trim((string) ($payload['name'] ?? ''));
        $level = $payload['level'] ?? null;

        if ($code === '' || $name === '' || !is_numeric($level)) {
            throw new HttpException('Invalid payload: code, name, and level are required.', 422);
        }

        if (in_array($code, RoleBootstrapper::protectedCodes(), true) && ((int) $level) <= 0) {
            throw new HttpException('Protected role must keep valid level.', 422);
        }

        return [
            'code' => $code,
            'name' => $name,
            'description' => isset($payload['description']) ? trim((string) $payload['description']) : null,
            'level' => (int) $level,
            'is_active' => filter_var($payload['is_active'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,
        ];
    }
}
