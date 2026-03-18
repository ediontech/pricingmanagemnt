<?php

declare(strict_types=1);

namespace App\Config;

final class Config
{
    public static function app(): App
    {
        return new App();
    }

    public static function database(): Database
    {
        return new Database();
    }

    /** @return array<int, array{0:string,1:string,2:string}> */
    public static function routes(): array
    {
        return require __DIR__ . '/Routes.php';
    }
}
