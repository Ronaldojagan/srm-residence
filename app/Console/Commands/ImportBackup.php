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

        $this->info("Preparing SQL statements...");
        $sql = File::get($path);

        // Strip MySQL table locks for TiDB / cloud compatibility
        $sql = preg_replace('/^LOCK TABLES[^;]*;/m', '', $sql);
        $sql = preg_replace('/^UNLOCK TABLES;/m', '', $sql);
        $sql = preg_replace('/^\/\*!40000 ALTER TABLE.*?DISABLE KEYS \*\/;/m', '', $sql);
        $sql = preg_replace('/^\/\*!40000 ALTER TABLE.*?ENABLE KEYS \*\/;/m', '', $sql);

        $this->info("Executing SQL statements...");
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::unprepared($sql);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $this->info("Database restored successfully with all tenants, rooms, billing, and settings!");
        return 0;
    }
}
