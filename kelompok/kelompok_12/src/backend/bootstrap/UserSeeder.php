<?php
declare(strict_types=1);

namespace Bootstrap;

use Models\User;
use Models\UserRole;

final class UserSeeder
{
    /**
     * Default users keyed by role code.
     */
    private const DEFAULT_USERS = [
        [
            'username' => 'owner_default',
            'email' => 'owner@example.com',
            'password' => 'owner123',
            'full_name' => 'Owner Default',
            'phone' => null,
            'role_code' => 'owner',
        ],
        [
            'username' => 'staff_default',
            'email' => 'staff@example.com',
            'password' => 'staff123',
            'full_name' => 'Staff Operasional',
            'phone' => '081200000001',
            'role_code' => 'staff',
        ],
        [
            'username' => 'member_default',
            'email' => 'member@example.com',
            'password' => 'member123',
            'full_name' => 'Member Sample',
            'phone' => '081200000002',
            'role_code' => 'member',
        ],
    ];

    public static function seedDefaults(): void
    {
        $userModel = new User();
        $roleModel = new UserRole();

        RoleBootstrapper::ensureDefaults();

        foreach (self::DEFAULT_USERS as $user) {
            $existing = $userModel->findByUsername($user['username']) ?? $userModel->findByEmail($user['email']);
            if ($existing !== null) {
                continue;
            }

            $role = $roleModel->findByCode($user['role_code']);
            if ($role === null) {
                continue;
            }

            $userModel->create([
                'username' => $user['username'],
                'email' => $user['email'],
                'password' => $user['password'],
                'full_name' => $user['full_name'],
                'phone' => $user['phone'],
                'role_id' => (int) $role['id'],
                'is_active' => true,
            ]);
        }
    }
}
