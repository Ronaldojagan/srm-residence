<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportBackup extends Command
{
    protected $signature = 'app:import-backup';
    protected $description = 'Import initial database backup SQL into the active database';

    public function handle()
    {
        $path = database_path('srm_residence_backup.sql');
        if (!File::exists($path)) {
            $this->error("Backup file not found at: {$path}");
            return 1;
        }

        $this->info("Reading backup file: {$path}...");
        $sql = File::get($path);

        $this->info("Executing SQL statements...");
        DB::unprepared($sql);

        $this->info("Database restored successfully with all tenants, rooms, billing, and settings!");
        return 0;
    }
}
