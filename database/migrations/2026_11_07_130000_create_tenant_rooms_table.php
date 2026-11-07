<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tenant_rooms')) {
            Schema::create('tenant_rooms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['tenant_id', 'room_id']);
            });

            // Populate tenant_rooms from existing tenants with room_id
            $existingTenants = DB::table('tenants')
                ->whereNotNull('room_id')
                ->get(['id', 'room_id', 'created_at', 'updated_at']);

            $records = [];
            $now = now();
            foreach ($existingTenants as $t) {
                // Ensure room exists
                $roomExists = DB::table('rooms')->where('id', $t->room_id)->exists();
                if ($roomExists) {
                    $records[] = [
                        'tenant_id' => $t->id,
                        'room_id' => $t->room_id,
                        'created_at' => $t->created_at ?? $now,
                        'updated_at' => $t->updated_at ?? $now,
                    ];
                }
            }

            if (!empty($records)) {
                DB::table('tenant_rooms')->insertOrIgnore($records);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_rooms');
    }
};
