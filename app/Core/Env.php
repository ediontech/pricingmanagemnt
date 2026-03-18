<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    /** @var array<string, string> */
    private static array $values = [];

    public static function load(string $basePath): void
    {
        if (self::$values !== []) {
            return;
        }

        $candidates = [$basePath . '/.env', $basePath . '/env'];
        $envPath = null;
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $envPath = $candidate;
                break;
            }
        }

        if ($envPath === null) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = preg_split('/\s*=\s*/', $line, 2);
            $key = trim((string) $key);
            $value = trim(trim((string) $value), "\"'");

            if ($key === '' || getenv($key) !== false) {
                continue;
            }

            self::$values[$key] = $value;
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $env = getenv($key);
        if ($env !== false) {
            return (string) $env;
        }

        return $_ENV[$key] ?? $_SERVER[$key] ?? self::$values[$key] ?? $default;
    }
}
