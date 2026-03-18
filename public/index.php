<?php

declare(strict_types=1);

use App\Config\Config;
use App\Controllers\PricingController;
use App\Core\Env;
use App\Models\RateRepository;

require dirname(__DIR__) . '/app/Core/helpers.php';
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__) . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

Env::load(dirname(__DIR__));

$controller = new PricingController(RateRepository::fromEnvironment());
$routes = Config::routes();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

foreach ($routes as [$routeMethod, $pattern, $action]) {
    if ($method === $routeMethod && preg_match($pattern, $path) === 1) {
        $controller->{$action}();
        exit;
    }
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'Not Found';
