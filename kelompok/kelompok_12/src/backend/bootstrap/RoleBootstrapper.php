<?php
declare(strict_types=1);

namespace Bootstrap;

use Models\UserRole;

final class RoleBootstrapper
{
    /**
     * Default roles that must always exist.
     */
    public const REQUIRED_ROLES = [
        [
            'code' => 'owner',
            'name' => 'Owner',
            'description' => 'Mengelola role pengguna dan membuat akun staff.',
            'level' => 1,
        ],
        [
            'code' => 'staff',
            'name' => 'Staff Operasional',
            'description' => 'Mengelola order dan transaksi harian.',
            'level' => 5,
        ],
        [
            'code' => 'member',
            'name' => 'Member/Pelanggan',
            'description' => 'Akses pelanggan dengan riwayat order.',
            'level' => 10,
        ],
    ];

    public static function ensureDefaults(): void
    {
        $model = new UserRole();

        foreach (self::REQUIRED_ROLES as $roleData) {
            $existing = $model->findByCode($roleData['code']);
            $payload = [
                'code' => $roleData['code'],
                'name' => $roleData['name'],
                'description' => $roleData['description'],
                'level' => $roleData['level'],
                'is_active' => true,
            ];

            if ($existing === null) {
                $model->create($payload);
            } else {
                $model->update((int) $existing['id'], $payload);
            }
        }
    }

    public static function protectedCodes(): array
    {
        static $codes = null;

        if ($codes === null) {
            $codes = array_column(self::REQUIRED_ROLES, 'code');
        }

        return $codes;
    }
}
