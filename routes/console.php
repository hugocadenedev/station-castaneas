<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('castaneas:purge-operations {--with-activity-log : Supprime aussi les logs d\'audit lies aux operations} {--force : Execute sans confirmation interactive}', function () {
    $tables = [
        'customer_order_palox',
        'customer_orders',
        'paloxes',
        'calibrations',
        'receptions',
    ];

    $activityTable = config('activitylog.table_name', 'activity_log');
    $deleteActivityLog = (bool) $this->option('with-activity-log');

    $this->warn('Cette action va vider les donnees operationnelles: commandes, stock palox, calibrages et receptions.');
    $this->line('Referentiels et utilisateurs conserves.');

    if ($deleteActivityLog) {
        $this->line('Les journaux d\'audit lies a ces operations seront aussi supprimes.');
    }

    if (! $this->option('force') && ! $this->confirm('Confirmer la purge des donnees operationnelles ?', false)) {
        $this->info('Purge annulee.');

        return self::SUCCESS;
    }

    Schema::disableForeignKeyConstraints();

    try {
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        if ($deleteActivityLog && Schema::hasTable($activityTable)) {
            DB::table($activityTable)
                ->whereIn('subject_type', [
                    'App\\Models\\Reception',
                    'App\\Models\\Calibration',
                    'App\\Models\\Palox',
                    'App\\Models\\CustomerOrder',
                ])
                ->delete();
        }
    } finally {
        Schema::enableForeignKeyConstraints();
    }

    $this->info('Purge terminee.');

    return self::SUCCESS;
})->purpose('Purge les donnees operationnelles Castaneas sans toucher aux referentiels');
