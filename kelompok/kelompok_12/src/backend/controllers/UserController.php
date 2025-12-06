<?php
declare(strict_types=1);

namespace Controllers;

use Core\HttpException;
use Core\Request;
use Core\Response;
use Models\User;
use Models\UserRole;

final class UserController
{
    public function __construct(
        private readonly User $users = new User(),
        private readonly UserRole $roles = new UserRole()
    ) {
    }

    public function index(Request $request, array $params = []): Response
    {
        return Response::json(['data' => $this->users->all()]);
    }

    public function show(Request $request, array $params = []): Response
    {
        $user = $this->users->find((int) $params['id']);
        if ($user === null) {
            throw new HttpException('User not found', 404);
        }

        return Response::json(['data' => $user]);
    }

    public function store(Request $request, array $params = []): Response
    {
        $payload = $this->validate($request->body(), false);
        $role = $this->roles->find($payload['role_id']);
        if ($role === null || $role['is_active'] === 0) {
            throw new HttpException('Role not found or inactive', 422);
        }

        $created = $this->users->create($payload);
        return Response::json(['message' => 'User created', 'data' => $created], 201);
    }

    public function update(Request $request, array $params = []): Response
    {
        $user = $this->users->find((int) $params['id']);
        if ($user === null) {
            throw new HttpException('User not found', 404);
        }

        $payload = $this->validate($request->body(), true);

        $role = $this->roles->find($payload['role_id']);
        if ($role === null || $role['is_active'] === 0) {
            throw new HttpException('Role not found or inactive', 422);
        }

        $updated = $this->users->update((int) $params['id'], $payload);
        return Response::json(['message' => 'User updated', 'data' => $updated]);
    }

    public function destroy(Request $request, array $params = []): Response
    {
        $user = $this->users->find((int) $params['id']);
        if ($user === null) {
            throw new HttpException('User not found', 404);
        }

        $this->users->delete((int) $params['id']);
        return Response::json(['message' => 'User deleted']);
    }

    private function validate(array $payload, bool $isUpdate): array
    {
        $username = trim((string) ($payload['username'] ?? ''));
        $email = filter_var($payload['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $fullName = trim((string) ($payload['full_name'] ?? ''));
        $roleId = $payload['role_id'] ?? null;

        if ($username === '' || $email === false || $fullName === '' || !is_numeric($roleId)) {
            throw new HttpException('Invalid payload: username, email, full_name, and role_id are required.', 422);
        }

        $password = $payload['password'] ?? null;
        if (!$isUpdate && (empty($password) || strlen((string) $password) < 6)) {
            throw new HttpException('Password is required and must be at least 6 characters.', 422);
        }

        if ($isUpdate && $password !== null && strlen((string) $password) < 6) {
            throw new HttpException('Password must be at least 6 characters.', 422);
        }

        return [
            'username' => $username,
            'email' => $email,
            'full_name' => $fullName,
            'phone' => isset($payload['phone']) ? trim((string) $payload['phone']) : null,
            'role_id' => (int) $roleId,
            'is_active' => filter_var($payload['is_active'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,
            'password' => $password,
        ];
    }
}
