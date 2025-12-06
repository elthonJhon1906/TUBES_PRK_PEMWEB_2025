<?php
declare(strict_types=1);

namespace Controllers;

use Core\HttpException;
use Core\Request;
use Core\Response;
use Models\User;
use Models\UserRole;

final class AuthController
{
    public function __construct(
        private readonly User $users = new User(),
        private readonly UserRole $roles = new UserRole()
    ) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function login(Request $request): Response
    {
        $username = trim((string) ($request->body()['username'] ?? ''));
        $password = (string) ($request->body()['password'] ?? '');

        if ($username === '' || $password === '') {
            throw new HttpException('Username dan password wajib diisi.', 422);
        }

        $record = $this->users->findByUsername($username);

        if ($record === null || (int) $record['is_active'] === 0) {
            throw new HttpException('Akun tidak ditemukan atau tidak aktif.', 401);
        }

        if (!password_verify($password, $record['password'])) {
            throw new HttpException('Kombinasi username/password salah.', 401);
        }

        $role = $this->roles->find((int) $record['role_id']);

        $_SESSION['user'] = [
            'id' => (int) $record['id'],
            'username' => $record['username'],
            'role' => $role ? $role['code'] : null,
        ];

        return Response::json(['message' => 'Login berhasil', 'data' => $_SESSION['user']]);
    }

    public function register(Request $request): Response
    {
        $body = $request->body();
        $username = trim((string) ($body['username'] ?? ''));
        $email = filter_var($body['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $fullName = trim((string) ($body['full_name'] ?? ''));
        $password = (string) ($body['password'] ?? '');

        if ($username === '' || $email === false || $fullName === '' || strlen($password) < 6) {
            throw new HttpException('Username, email valid, full_name, dan password (>=6) wajib diisi.', 422);
        }

        $memberRole = $this->roles->findByCode('member');
        if ($memberRole === null || (int) $memberRole['is_active'] === 0) {
            throw new HttpException('Role member tidak tersedia.', 500);
        }

        $created = $this->users->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $fullName,
            'phone' => isset($body['phone']) ? trim((string) $body['phone']) : null,
            'role_id' => (int) $memberRole['id'],
            'is_active' => true,
        ]);

        $_SESSION['user'] = [
            'id' => (int) $created['id'],
            'username' => $created['username'],
            'role' => $memberRole['code'],
        ];

        return Response::json(['message' => 'Registrasi berhasil', 'data' => $_SESSION['user']], 201);
    }

    public function logout(Request $request): Response
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_unset();
        session_destroy();

        return Response::json(['message' => 'Logout berhasil']);
    }
}
