<?php

use App\Services\SqliteToMysqlMigrator;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:migrate-sqlite {--source= : SQLite file path} {--no-truncate : Keep existing MySQL rows}', function () {
    $source = $this->option('source') ?: database_path('database.sqlite');
    $truncate = ! $this->option('no-truncate');

    $results = app(SqliteToMysqlMigrator::class)->import($source, $truncate);

    if ($results === []) {
        $this->components->warn('No application tables were found in the SQLite source.');

        return;
    }

    foreach ($results as $table => $count) {
        $this->components->info(sprintf('%s: %d rows copied', $table, $count));
    }

    $this->components->info('SQLite data has been copied into MySQL.');
})->purpose('Copy application data from SQLite into MySQL');
