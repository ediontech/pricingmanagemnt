<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\RateRepository;

final class PricingController
{
    public function __construct(private readonly RateRepository $rates)
    {
    }

    public function index(): void
    {
        $app = Config::app();

        View::render('pricing/index', [
            'title' => $app->appName,
            'defaultYear' => (int) $app->defaultYear,
            'defaultMonth' => (int) $app->defaultMonth,
            'defaultCodes' => array_values(array_filter(array_map('trim', explode(',', $app->defaultAcriss)))),
        ]);
    }

    public function dashboard(): void
    {
        $year = max(2025, (int) ($_GET['year'] ?? 2026));
        $month = min(12, max(1, (int) ($_GET['month'] ?? 2)));
        $codes = isset($_GET['acriss']) ? array_values(array_filter(explode(',', (string) $_GET['acriss']))) : ['CCAR'];

        Response::json([
            'meta' => [
                'year' => $year,
                'month' => $month,
                'acriss_codes' => $codes,
            ],
            'rows' => $this->rates->dashboardData($year, $month, $codes),
            'history' => $this->rates->history(8),
        ]);
    }

    public function bulkUpdate(): void
    {
        $payload = Request::json();
        $updated = $this->rates->bulkUpdate($payload);

        Response::json([
            'message' => 'Bulk update applied successfully.',
            'updated_rows' => $updated,
        ]);
    }

    public function copyRates(): void
    {
        $payload = Request::json();
        $updated = $this->rates->copyFirstDayRates((int) ($payload['year'] ?? 2026), (int) ($payload['month'] ?? 2), $payload['acriss_codes'] ?? ['CCAR']);

        Response::json([
            'message' => 'First-day rates copied across rental durations.',
            'updated_rows' => $updated,
        ]);
    }

    public function integrity(): void
    {
        $year = (int) ($_GET['year'] ?? 2026);
        $month = (int) ($_GET['month'] ?? 2);
        $codes = isset($_GET['acriss']) ? array_values(array_filter(explode(',', (string) $_GET['acriss']))) : ['CCAR'];

        Response::json($this->rates->integrityCheck($year, $month, $codes));
    }

    public function history(): void
    {
        Response::json([
            'items' => $this->rates->history(20),
        ]);
    }

    public function toggleLock(): void
    {
        $payload = Request::json();
        $updated = $this->rates->toggleLock($payload['row_ids'] ?? [], (bool) ($payload['locked'] ?? true));

        Response::json([
            'message' => 'Row lock state updated.',
            'updated_rows' => $updated,
        ]);
    }
}
