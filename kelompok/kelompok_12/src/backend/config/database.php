<?php
declare(strict_types=1);


final class Database
{
    private const DEFAULT_DRIVER = 'mysql';
    private const DEFAULT_HOST = '127.0.0.1';
    private const DEFAULT_PORT = '3306';
    private const DEFAULT_DB = 'npc';
    private const DEFAULT_CHARSET = 'utf8mb4';

    private static ?PDO $connection = null;

    /**
     * Returns a shared PDO connection instance.
     *
     * @throws PDOException when the connection cannot be established.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = self::resolveConfig();
        $dsn = sprintf(
            '%s:host=%s;dbname=%s;port=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['database'],
            $config['port'],
            $config['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        self::$connection = new PDO($dsn, $config['username'], $config['password'], $options);

        return self::$connection;
    }

    /**
     * Clears the cached PDO instance (helpful for long running scripts/tests).
     */
    public static function reset(): void
    {
        self::$connection = null;
    }

    /**
     * Resolves DB configuration from environment variables with sensible defaults.
     */
    private static function resolveConfig(): array
    {
        return [
            'driver' => self::env('DB_DRIVER', self::DEFAULT_DRIVER),
            'host' => self::env('DB_HOST', self::DEFAULT_HOST),
            'port' => self::env('DB_PORT', self::DEFAULT_PORT),
            'database' => self::env('DB_DATABASE', self::DEFAULT_DB),
            'username' => self::env('DB_USERNAME', 'elthon'),
            'password' => self::env('DB_PASSWORD', 'tebing123'),
            'charset' => self::env('DB_CHARSET', self::DEFAULT_CHARSET),
        ];
    }

    private static function env(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }
}
