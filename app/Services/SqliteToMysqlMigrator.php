<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use RuntimeException;

class SqliteToMysqlMigrator
{
    private const EXCLUDED_TABLES = [
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'migrations',
        'password_reset_tokens',
        'sessions',
    ];

    private const TABLE_ORDER = [
        'companies',
        'users',
        'item_categories',
        'units',
        'customers',
        'items',
        'document_templates',
        'template_mappings',
        'transactions',
        'transaction_details',
        'transaction_brought_items',
        'print_logs',
        'audit_logs',
    ];

    public function import(string $sourcePath, bool $truncate = true, int $chunkSize = 500): array
    {
        if (! is_file($sourcePath)) {
            throw new RuntimeException("SQLite source file not found: {$sourcePath}");
        }

        $source = new PDO('sqlite:'.$sourcePath);
        $source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $source->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $tables = $this->orderedTables($this->fetchTables($source));

        if ($tables === []) {
            return [];
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            if ($truncate) {
                foreach (array_reverse($tables) as $table) {
                    if (Schema::hasTable($table)) {
                        DB::statement("TRUNCATE TABLE `{$table}`");
                    }
                }
            }

            $results = [];

            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    throw new RuntimeException("Target MySQL table is missing: {$table}");
                }

                $rows = $this->fetchRows($source, $table);
                $count = count($rows);

                if ($count === 0) {
                    $results[$table] = 0;
                    continue;
                }

                foreach (array_chunk($rows, $chunkSize) as $chunk) {
                    DB::table($table)->insert($chunk);
                }

                $results[$table] = $count;
            }

            return $results;
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * @return array<int, string>
     */
    private function fetchTables(PDO $source): array
    {
        $statement = $source->query(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
        );

        $tables = [];

        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $table) {
            if (! in_array($table, self::EXCLUDED_TABLES, true)) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * @param  array<int, string>  $tables
     * @return array<int, string>
     */
    private function orderedTables(array $tables): array
    {
        $ordered = [];

        foreach (self::TABLE_ORDER as $table) {
            if (in_array($table, $tables, true)) {
                $ordered[] = $table;
            }
        }

        foreach ($tables as $table) {
            if (! in_array($table, $ordered, true)) {
                $ordered[] = $table;
            }
        }

        return $ordered;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchRows(PDO $source, string $table): array
    {
        $statement = $source->query(sprintf('SELECT * FROM "%s"', str_replace('"', '""', $table)));

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
