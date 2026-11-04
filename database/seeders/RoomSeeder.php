<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $roomsData = [
            // 1st Floor (8 Rooms)
            ['room_number' => '101', 'floor' => '1st Floor', 'room_type' => 'Single Deluxe', 'capacity' => 1, 'monthly_rent' => 12000, 'security_deposit' => 15000, 'occupancy_status' => 'occupied', 'amenities' => 'AC, Attached Bath, Wi-Fi, Balcony, Study Table'],
            ['room_number' => '102', 'floor' => '1st Floor', 'room_type' => 'Single Deluxe', 'capacity' => 1, 'monthly_rent' => 12000, 'security_deposit' => 15000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, Wardrobe'],
            ['room_number' => '103', 'floor' => '1st Floor', 'room_type' => 'Double Sharing', 'capacity' => 2, 'monthly_rent' => 8500, 'security_deposit' => 10000, 'occupancy_status' => 'occupied', 'amenities' => 'AC, Attached Bath, Wi-Fi, 2 Wardrobes'],
            ['room_number' => '104', 'floor' => '1st Floor', 'room_type' => 'Double Sharing', 'capacity' => 2, 'monthly_rent' => 8500, 'security_deposit' => 10000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, 2 Wardrobes'],
            ['room_number' => '105', 'floor' => '1st Floor', 'room_type' => 'Double Sharing', 'capacity' => 2, 'monthly_rent' => 8500, 'security_deposit' => 10000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, 2 Wardrobes'],
            ['room_number' => '106', 'floor' => '1st Floor', 'room_type' => 'Triple Sharing', 'capacity' => 3, 'monthly_rent' => 6500, 'security_deposit' => 8000, 'occupancy_status' => 'available', 'amenities' => 'Non-AC, Attached Bath, Wi-Fi, 3 Wardrobes'],
            ['room_number' => '107', 'floor' => '1st Floor', 'room_type' => 'Triple Sharing', 'capacity' => 3, 'monthly_rent' => 6500, 'security_deposit' => 8000, 'occupancy_status' => 'available', 'amenities' => 'Non-AC, Attached Bath, Wi-Fi, 3 Wardrobes'],
            ['room_number' => '108', 'floor' => '1st Floor', 'room_type' => 'Single Deluxe', 'capacity' => 1, 'monthly_rent' => 12000, 'security_deposit' => 15000, 'occupancy_status' => 'occupied', 'amenities' => 'AC, Attached Bath, Wi-Fi, Balcony'],

            // 2nd Floor (8 Rooms)
            ['room_number' => '201', 'floor' => '2nd Floor', 'room_type' => 'Single Deluxe', 'capacity' => 1, 'monthly_rent' => 12000, 'security_deposit' => 15000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, Balcony, Smart TV'],
            ['room_number' => '202', 'floor' => '2nd Floor', 'room_type' => 'Double Sharing', 'capacity' => 2, 'monthly_rent' => 8500, 'security_deposit' => 10000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, 2 Wardrobes'],
            ['room_number' => '203', 'floor' => '2nd Floor', 'room_type' => 'Double Sharing', 'capacity' => 2, 'monthly_rent' => 8500, 'security_deposit' => 10000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, 2 Wardrobes'],
            ['room_number' => '204', 'floor' => '2nd Floor', 'room_type' => 'Executive Suite', 'capacity' => 1, 'monthly_rent' => 16000, 'security_deposit' => 20000, 'occupancy_status' => 'occupied', 'amenities' => 'AC, Attached Bath, Wi-Fi, Balcony, Mini Fridge, Sofa'],
            ['room_number' => '205', 'floor' => '2nd Floor', 'room_type' => 'Double Sharing', 'capacity' => 2, 'monthly_rent' => 8500, 'security_deposit' => 10000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, 2 Wardrobes'],
            ['room_number' => '206', 'floor' => '2nd Floor', 'room_type' => 'Triple Sharing', 'capacity' => 3, 'monthly_rent' => 6500, 'security_deposit' => 8000, 'occupancy_status' => 'available', 'amenities' => 'Non-AC, Attached Bath, Wi-Fi, 3 Wardrobes'],
            ['room_number' => '207', 'floor' => '2nd Floor', 'room_type' => 'Double Sharing', 'capacity' => 2, 'monthly_rent' => 8500, 'security_deposit' => 10000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, 2 Wardrobes'],
            ['room_number' => '208', 'floor' => '2nd Floor', 'room_type' => 'Single Deluxe', 'capacity' => 1, 'monthly_rent' => 12000, 'security_deposit' => 15000, 'occupancy_status' => 'maintenance', 'amenities' => 'AC, Attached Bath, Wi-Fi, Balcony (Under Repainting)'],

            // 3rd Floor (7 Rooms) -> Total = 23 rooms
            ['room_number' => '301', 'floor' => '3rd Floor', 'room_type' => 'Single Deluxe', 'capacity' => 1, 'monthly_rent' => 12000, 'security_deposit' => 15000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, Balcony, Terrace View'],
            ['room_number' => '302', 'floor' => '3rd Floor', 'room_type' => 'Double Sharing', 'capacity' => 2, 'monthly_rent' => 8500, 'security_deposit' => 10000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, 2 Wardrobes'],
            ['room_number' => '303', 'floor' => '3rd Floor', 'room_type' => 'Double Sharing', 'capacity' => 2, 'monthly_rent' => 8500, 'security_deposit' => 10000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, 2 Wardrobes'],
            ['room_number' => '304', 'floor' => '3rd Floor', 'room_type' => 'Executive Suite', 'capacity' => 1, 'monthly_rent' => 16000, 'security_deposit' => 20000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, Balcony, Terrace View, Mini Fridge'],
            ['room_number' => '305', 'floor' => '3rd Floor', 'room_type' => 'Triple Sharing', 'capacity' => 3, 'monthly_rent' => 6500, 'security_deposit' => 8000, 'occupancy_status' => 'available', 'amenities' => 'Non-AC, Attached Bath, Wi-Fi, 3 Wardrobes'],
            ['room_number' => '306', 'floor' => '3rd Floor', 'room_type' => 'Double Sharing', 'capacity' => 2, 'monthly_rent' => 8500, 'security_deposit' => 10000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, 2 Wardrobes'],
            ['room_number' => '307', 'floor' => '3rd Floor', 'room_type' => 'Single Deluxe', 'capacity' => 1, 'monthly_rent' => 12000, 'security_deposit' => 15000, 'occupancy_status' => 'available', 'amenities' => 'AC, Attached Bath, Wi-Fi, Balcony'],
        ];

        foreach ($roomsData as $item) {
            Room::updateOrCreate(['room_number' => $item['room_number']], $item);
        }
    }
}
