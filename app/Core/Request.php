<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @return array<string, mixed> */
    public static function json(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
