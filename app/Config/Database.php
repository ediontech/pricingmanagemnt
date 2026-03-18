<?php

declare(strict_types=1);

namespace App\Config;

use App\Core\Env;

final class Database
{
    /** @var array<string, string> */
    public array $defaultGroup;

    public function __construct()
    {
        $this->defaultGroup = [
            'DBDriver' => Env::get('DB_CONNECTION', Env::get('database.default.DBDriver', 'MySQLi')) ?? 'MySQLi',
            'hostname' => Env::get('DB_HOST', Env::get('database.default.hostname', '127.0.0.1')) ?? '127.0.0.1',
            'port' => Env::get('DB_PORT', Env::get('database.default.port', '3306')) ?? '3306',
            'database' => Env::get('DB_DATABASE', Env::get('database.default.database', 'pricing_management')) ?? 'pricing_management',
            'username' => Env::get('DB_USERNAME', Env::get('database.default.username', 'root')) ?? 'root',
            'password' => Env::get('DB_PASSWORD', Env::get('database.default.password', '')) ?? '',
            'charset' => Env::get('database.default.charset', 'utf8mb4') ?? 'utf8mb4',
            'DBCollat' => Env::get('database.default.DBCollat', 'utf8mb4_unicode_ci') ?? 'utf8mb4_unicode_ci',
        ];
    }
}
