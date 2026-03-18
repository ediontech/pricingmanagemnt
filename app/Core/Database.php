<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\Config;
use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = Config::database()->defaultGroup;
        $driver = strtolower($config['DBDriver']);

        if ($driver === 'sqlite3' || $driver === 'sqlite') {
            $database = $config['database'] !== '' ? $config['database'] : base_path('database/database.sqlite');
            self::$connection = new PDO('sqlite:' . $database);
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['hostname'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $exception) {
                throw new PDOException('Database connection failed: ' . $exception->getMessage(), (int) $exception->getCode(), $exception);
            }
        }

        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return self::$connection;
    }
}
