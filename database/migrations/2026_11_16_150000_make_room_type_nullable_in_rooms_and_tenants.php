<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('rooms', 'room_type')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('room_type')->nullable()->change();
            });
        }

        if (Schema::hasColumn('tenants', 'room_type')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('room_type')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('rooms', 'room_type')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('room_type')->nullable(false)->change();
            });
        }

        if (Schema::hasColumn('tenants', 'room_type')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('room_type')->nullable(false)->change();
            });
        }
    }
};
