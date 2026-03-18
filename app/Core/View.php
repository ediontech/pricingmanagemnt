<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = [], string $layout = 'layouts/app'): void
    {
        $viewsPath = dirname(__DIR__) . '/Views';
        extract($data, EXTR_SKIP);

        ob_start();
        require $viewsPath . '/' . $template . '.php';
        $content = (string) ob_get_clean();

        require $viewsPath . '/' . $layout . '.php';
    }
}
