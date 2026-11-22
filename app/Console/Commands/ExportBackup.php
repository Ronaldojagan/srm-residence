<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ExportBackup extends Command
{
    protected $signature = 'app:export-backup {--path= : Custom output file path}';
    protected $description = 'Export all live database tables into a clean SQL backup file';

    public function handle()
    {
        $tables = [
            'users',
            'applications',
            'banners',
            'rooms',
            'tenants',
            'tenant_rooms',
            'bills',
            'bill_payments',
            'expenses',
            'loans',
            'loan_emis',
            'salaries',
            'journal_entries',
            'settings'
        ];

        $this->info("Starting database export across " . count($tables) . " core tables...");

        $sql = "-- SRM RESIDENCE COMPLETE DATABASE DUMP\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        $totalRows = 0;

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->warn("Table '{$table}' does not exist, skipping.");
                continue;
            }

            $rows = DB::table($table)->get();
            $count = $rows->count();
            $totalRows += $count;

            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table: `{$table}` ({$count} rows)\n";
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "TRUNCATE TABLE `{$table}`;\n";

            if ($count === 0) {
                $sql .= "\n";
                continue;
            }

            foreach ($rows->chunk(50) as $chunk) {
                $first = true;
                foreach ($chunk as $row) {
                    $rowArray = (array) $row;
                    if ($first) {
                        $cols = array_map(function($c) { return "`{$c}`"; }, array_keys($rowArray));
                        $sql .= "INSERT INTO `{$table}` (" . implode(', ', $cols) . ") VALUES\n";
                        $first = false;
                    } else {
                        $sql .= ",\n";
                    }

                    $vals = array_map(function($val) {
                        if (is_null($val)) return "NULL";
                        return "'" . addslashes((string)$val) . "'";
                    }, array_values($rowArray));

                    $sql .= "(" . implode(', ', $vals) . ")";
                }
                $sql .= ";\n";
            }
            $sql .= "\n";
            $this->line(" - Table `{$table}`: {$count} rows exported.");
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $sql .= "-- Dump completed. Total rows exported: {$totalRows}\n";

        $outputPath = $this->option('path') ?: database_path('srm_residence_backup.sql');
        File::put($outputPath, $sql);

        $this->info("\nSUCCESS: Exported {$totalRows} rows across all tables to:\n{$outputPath}");
        return 0;
    }
}
