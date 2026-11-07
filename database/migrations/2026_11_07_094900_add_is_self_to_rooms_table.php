<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter enum to include 'self' for owner-occupied rooms
        DB::statement("ALTER TABLE rooms MODIFY COLUMN occupancy_status ENUM('available', 'occupied', 'maintenance', 'self') NOT NULL DEFAULT 'available'");

        if (!Schema::hasColumn('rooms', 'is_self')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->boolean('is_self')->default(false)->after('occupancy_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('rooms', 'is_self')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('is_self');
            });
        }

        DB::statement("ALTER TABLE rooms MODIFY COLUMN occupancy_status ENUM('available', 'occupied', 'maintenance') NOT NULL DEFAULT 'available'");
    }
};
