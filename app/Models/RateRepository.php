<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use PDO;

final class RateRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? new PDO('sqlite::memory:');
        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' && $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='rate_rows'")->fetchColumn() === false) {
            $this->db->exec('CREATE TABLE IF NOT EXISTS rate_rows (id INTEGER PRIMARY KEY AUTOINCREMENT, pickup_date TEXT NOT NULL, acriss_code TEXT NOT NULL, day_1 REAL NOT NULL, day_2 REAL NOT NULL, day_3 REAL NOT NULL, day_4 REAL NOT NULL, day_5 REAL NOT NULL, day_6 REAL NOT NULL, day_7 REAL NOT NULL, day_8 REAL NOT NULL, day_9 REAL NOT NULL, day_10 REAL NOT NULL, day_11 REAL NOT NULL, day_12 REAL NOT NULL, day_13 REAL NOT NULL, day_14 REAL NOT NULL, day_15 REAL NOT NULL, day_16 REAL NOT NULL, is_locked INTEGER NOT NULL DEFAULT 0, created_at TEXT DEFAULT CURRENT_TIMESTAMP, updated_at TEXT DEFAULT CURRENT_TIMESTAMP)');
            $this->db->exec('CREATE TABLE IF NOT EXISTS rate_history (id INTEGER PRIMARY KEY AUTOINCREMENT, action TEXT NOT NULL, details TEXT, context_payload TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP)');
            $this->seedSqlite();
        }
    }

    public static function fromEnvironment(): self
    {
        return new self(Database::connection());
    }

    /** @return array<int, array<string, mixed>> */
    public function dashboardData(int $year, int $month, array $codes): array
    {
        $codes = $codes === [] ? ['CCAR'] : $codes;
        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = (new DateTimeImmutable($start))->modify('last day of this month')->format('Y-m-d');

        $statement = $this->db->prepare(
            "SELECT id, pickup_date, acriss_code, is_locked, day_1, day_2, day_3, day_4, day_5, day_6, day_7, day_8, day_9, day_10, day_11, day_12, day_13, day_14, day_15, day_16
             FROM rate_rows
             WHERE pickup_date BETWEEN ? AND ? AND acriss_code IN ($placeholders)
             ORDER BY pickup_date ASC, acriss_code ASC"
        );

        $statement->execute(array_merge([$start, $end], $codes));
        return $statement->fetchAll() ?: [];
    }

    /** @return array<int, array<string, mixed>> */
    public function history(int $limit = 20): array
    {
        $statement = $this->db->prepare('SELECT id, action, details, context_payload, created_at FROM rate_history ORDER BY id DESC LIMIT ?');
        $statement->bindValue(1, $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }

    /** @return array<string, mixed> */
    public function integrityCheck(int $year, int $month, array $codes): array
    {
        $rows = $this->dashboardData($year, $month, $codes);
        $issues = [];

        foreach ($rows as $row) {
            for ($day = 1; $day <= 16; $day++) {
                $value = (float) $row['day_' . $day];
                if ($value < 0 || $value > 3000) {
                    $issues[] = [
                        'row_id' => (int) $row['id'],
                        'pickup_date' => $row['pickup_date'],
                        'acriss_code' => $row['acriss_code'],
                        'column' => 'day_' . $day,
                        'value' => $value,
                    ];
                }
            }
        }

        $this->log('Integrity Check', $issues === [] ? 'No issues found' : count($issues) . ' issue(s) detected');

        return [
            'issues' => $issues,
            'issue_count' => count($issues),
        ];
    }

    /** @param array<string, mixed> $payload */
    public function bulkUpdate(array $payload): int
    {
        $year = (int) ($payload['year'] ?? date('Y'));
        $month = (int) ($payload['month'] ?? date('n'));
        $codes = array_values(array_filter($payload['acriss_codes'] ?? []));
        $operation = trim((string) ($payload['operation'] ?? ''));
        $selectedDays = array_map('intval', $payload['selected_days'] ?? range(1, 31));

        $rows = $this->dashboardData($year, $month, $codes);
        $updated = 0;
        foreach ($rows as $row) {
            $pickupDay = (int) date('j', strtotime((string) $row['pickup_date']));
            if (!in_array($pickupDay, $selectedDays, true) || (int) $row['is_locked'] === 1) {
                continue;
            }

            for ($day = 1; $day <= 16; $day++) {
                $column = 'day_' . $day;
                $next = $this->applyOperation((float) $row[$column], $operation);
                $statement = $this->db->prepare("UPDATE rate_rows SET $column = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $statement->execute([$next, $row['id']]);
            }
            $updated++;
        }

        $this->log('Bulk Update', sprintf('Operation %s applied to %d row(s)', $operation, $updated), $payload);
        return $updated;
    }

    public function copyFirstDayRates(int $year, int $month, array $codes): int
    {
        $rows = $this->dashboardData($year, $month, $codes);
        $updated = 0;
        foreach ($rows as $row) {
            if ((int) $row['is_locked'] === 1) {
                continue;
            }
            $seed = (float) $row['day_1'];
            $assignments = [];
            $values = [];
            for ($day = 1; $day <= 16; $day++) {
                $assignments[] = 'day_' . $day . ' = ?';
                $values[] = $seed;
            }
            $values[] = $row['id'];
            $statement = $this->db->prepare('UPDATE rate_rows SET ' . implode(', ', $assignments) . ', updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $statement->execute($values);
            $updated++;
        }

        $this->log('Copy Rates', sprintf('Copied first-day rate across %d row(s)', $updated));
        return $updated;
    }

    public function toggleLock(array $rowIds, bool $locked): int
    {
        if ($rowIds === []) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($rowIds), '?'));
        $statement = $this->db->prepare("UPDATE rate_rows SET is_locked = ?, updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)");
        $statement->execute(array_merge([$locked ? 1 : 0], $rowIds));
        $count = $statement->rowCount();
        $this->log($locked ? 'Lock Rows' : 'Unlock Rows', sprintf('%d row(s) updated', $count), ['row_ids' => $rowIds]);
        return $count;
    }

    private function applyOperation(float $current, string $operation): float
    {
        if (preg_match('/^[+-]?\d+(?:\.\d+)?%$/', $operation) === 1) {
            return max(0, round($current + ($current * ((float) $operation / 100)), 2));
        }

        if (preg_match('/^[+-]\d+(?:\.\d+)?$/', $operation) === 1) {
            return max(0, round($current + (float) $operation, 2));
        }

        if (preg_match('/^\d+(?:\.\d+)?$/', $operation) === 1) {
            return round((float) $operation, 2);
        }

        return $current;
    }

    /** @param array<string, mixed>|null $context */
    private function log(string $action, string $details, ?array $context = null): void
    {
        $statement = $this->db->prepare('INSERT INTO rate_history (action, details, context_payload, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)');
        $statement->execute([$action, $details, $context === null ? null : json_encode($context)]);
    }

    private function seedSqlite(): void
    {
        $start = new DateTimeImmutable('2026-02-01');
        $period = new DatePeriod($start, new DateInterval('P1D'), 28);
        $codes = ['CCAR', 'ECAR', 'FFAR'];
        $statement = $this->db->prepare('INSERT INTO rate_rows (pickup_date, acriss_code, day_1, day_2, day_3, day_4, day_5, day_6, day_7, day_8, day_9, day_10, day_11, day_12, day_13, day_14, day_15, day_16, is_locked) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $index = 0;
        foreach ($period as $date) {
            foreach ($codes as $code) {
                $base = 45 + ($index % 7) * 8 + ($code === 'FFAR' ? 30 : ($code === 'ECAR' ? 10 : 0));
                $values = [$date->format('Y-m-d'), $code];
                for ($day = 1; $day <= 16; $day++) {
                    $values[] = round($base + ($day * 2.75), 2);
                }
                $values[] = $index < 5 ? 1 : 0;
                $statement->execute($values);
                $index++;
            }
        }

        $this->log('Seed Data', 'SQLite demo data loaded');
    }
}
