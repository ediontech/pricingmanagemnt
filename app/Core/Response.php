<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    /** @param array<string, mixed> $data */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
