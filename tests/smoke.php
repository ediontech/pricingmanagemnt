<?php

declare(strict_types=1);

$base = dirname(__DIR__);
$public = $base . '/public';
$command = sprintf('APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=%s php -S 127.0.0.1:8099 -t %s >/tmp/pricing-smoke.log 2>&1 & echo $!', escapeshellarg('/tmp/pricing-smoke.sqlite'), escapeshellarg($public));
$pid = (int) shell_exec($command);

if ($pid <= 0) {
    fwrite(STDERR, "Failed to start PHP test server.\n");
    exit(1);
}

register_shutdown_function(static function () use ($pid): void {
    exec('kill ' . $pid . ' >/dev/null 2>&1');
});

usleep(800000);

$home = @file_get_contents('http://127.0.0.1:8099/');
if ($home === false || !str_contains($home, 'Backoffice Rate Manager')) {
    fwrite(STDERR, "Homepage smoke test failed.\n");
    exit(1);
}

$dashboard = @file_get_contents('http://127.0.0.1:8099/api/dashboard?year=2026&month=2&acriss=CCAR');
if ($dashboard === false) {
    fwrite(STDERR, "Dashboard endpoint unavailable.\n");
    exit(1);
}

$payload = json_decode($dashboard, true);
if (!is_array($payload) || !isset($payload['rows'][0]['pickup_date'])) {
    fwrite(STDERR, "Dashboard payload shape invalid.\n");
    exit(1);
}

echo "Smoke tests passed.\n";
