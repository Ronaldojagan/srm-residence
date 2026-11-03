<?php

namespace Database\Seeders;

use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    public function run(): void
    {
        $couriers = [
            [
                'name' => 'Delhivery',
                'tracking_url_template' => 'https://www.delhivery.com/track/package/{tracking_number}',
                'estimated_delivery_days' => 3,
                'auto_update_delivery' => true,
                'auto_update_delay_hours' => 24,
                'contact_number' => '1800 258 8888',
                'website' => 'https://www.delhivery.com',
                'is_active' => true
            ],
            [
                'name' => 'Blue Dart',
                'tracking_url_template' => 'https://www.bluedart.com/track/{tracking_number}',
                'estimated_delivery_days' => 2,
                'auto_update_delivery' => true,
                'auto_update_delay_hours' => 12,
                'contact_number' => '1800 233 1234',
                'website' => 'https://www.bluedart.com',
                'is_active' => true
            ],
            [
                'name' => 'ST Courier',
                'tracking_url_template' => 'https://www.stcourier.in/track/{tracking_number}',
                'estimated_delivery_days' => 3,
                'auto_update_delivery' => true,
                'auto_update_delay_hours' => 24,
                'contact_number' => '1800 123 4567',
                'website' => 'https://www.stcourier.in',
                'is_active' => true
            ],
            [
                'name' => 'DTDC',
                'tracking_url_template' => 'https://www.dtdc.com/tracking.asp?tran_type=AWB&strCnno={tracking_number}',
                'estimated_delivery_days' => 4,
                'auto_update_delivery' => true,
                'auto_update_delay_hours' => 24,
                'contact_number' => '1800 208 2080',
                'website' => 'https://www.dtdc.com',
                'is_active' => true
            ],
            [
                'name' => 'Ecom Express',
                'tracking_url_template' => 'https://ecomexpress.in/tracking/?awb_field={tracking_number}',
                'estimated_delivery_days' => 5,
                'auto_update_delivery' => true,
                'auto_update_delay_hours' => 24,
                'contact_number' => '1800 102 3838',
                'website' => 'https://ecomexpress.in',
                'is_active' => true
            ],
            [
                'name' => 'Shadowfax',
                'tracking_url_template' => '#',
                'estimated_delivery_days' => 1,
                'auto_update_delivery' => true,
                'auto_update_delay_hours' => 6,
                'contact_number' => '080 6909 2211',
                'website' => 'https://shadowfax.in',
                'is_active' => true
            ],
            [
                'name' => 'XpressBees',
                'tracking_url_template' => 'https://track.xpressbees.com/?ref={tracking_number}',
                'estimated_delivery_days' => 4,
                'auto_update_delivery' => true,
                'auto_update_delay_hours' => 24,
                'contact_number' => '1800 419 4422',
                'website' => 'https://www.xpressbees.com',
                'is_active' => true
            ],
            [
                'name' => 'Local Delivery',
                'tracking_url_template' => '#',
                'estimated_delivery_days' => 1,
                'auto_update_delivery' => true,
                'auto_update_delay_hours' => 8,
                'contact_number' => '',
                'website' => '',
                'is_active' => true
            ]
        ];

        foreach ($couriers as $courier) {
            Courier::updateOrCreate(
                ['name' => $courier['name']],
                $courier
            );
        }
    }
}
