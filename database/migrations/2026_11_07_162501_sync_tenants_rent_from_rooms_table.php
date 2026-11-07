<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tenants = DB::table('tenants')->get();

        foreach ($tenants as $t) {
            // Find assigned rooms via tenant_rooms pivot or room_id
            $roomIds = DB::table('tenant_rooms')
                ->where('tenant_id', $t->id)
                ->pluck('room_id')
                ->toArray();

            if (empty($roomIds) && $t->room_id) {
                $roomIds = [$t->room_id];
            }

            if (!empty($roomIds)) {
                $rooms = DB::table('rooms')->whereIn('id', $roomIds)->get();

                if ($rooms->isNotEmpty()) {
                    $monthlyRent = (float) $rooms->sum('monthly_rent');
                    $securityDeposit = (float) $rooms->sum('security_deposit');
                    $maintenanceCharges = (float) $rooms->sum('maintenance_charges');
                    $otherCharges = (float) $rooms->sum('other_charges');
                    $ebRate = (float) ($rooms->first()->eb_unit_rate ?? 10.00);

                    DB::table('tenants')->where('id', $t->id)->update([
                        'monthly_rent' => $monthlyRent,
                        'security_deposit' => $securityDeposit,
                        'maintenance_charges' => $maintenanceCharges,
                        'other_charges' => $otherCharges,
                        'eb_unit_rate' => $ebRate,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No reverse needed as this is data synchronization
    }
};
