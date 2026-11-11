<?php

namespace Database\Seeders;

use App\Enums\RoomCategory;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

/**
 * Room types from the frontend draft (src/data/content.ts), a few rooms each and
 * one sample seasonal rate. Safe to re-run: rows are matched by slug / name.
 * Room counts and max guests are placeholders until Arif confirms them.
 */
class RoomCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->roomTypes() as $sortOrder => $data) {
            $rooms = $data['rooms'];
            unset($data['rooms']);

            $roomType = RoomType::updateOrCreate(
                ['slug' => $data['slug']],
                [...$data, 'sort_order' => $sortOrder, 'is_active' => true],
            );

            foreach ($rooms as $name) {
                $roomType->rooms()->updateOrCreate(['name' => $name], ['is_active' => true]);
            }
        }

        $this->seedWinterRate();
    }

    private function roomTypes(): array
    {
        return [
            [
                'name' => 'Premium Riverview AC Suite',
                'slug' => 'premium-riverview-ac-suite',
                'category' => RoomCategory::Ac,
                'vibe_tag' => 'romance',
                'tagline' => 'River & Forest Panorama',
                'description' => 'Our signature couple-friendly haven: teak interior, king bedding, hot shower and a private wooden terrace with a hanging swing over the Passur river.',
                'amenities' => ['AC', 'Free WiFi', 'Hot Rain Shower', 'Private Balcony Swing'],
                'base_price' => 7500,
                'max_guests' => 3,
                'image_path' => '/images/cottage_luxury_suite.webp',
                'rooms' => ['R-101', 'R-102', 'R-103', 'R-104'],
            ],
            [
                'name' => 'Traditional Mangrove Eco',
                'slug' => 'traditional-mangrove-eco',
                'category' => RoomCategory::NonAc,
                'vibe_tag' => 'mindful',
                'tagline' => 'Authentic Golpata Eco',
                'description' => 'Natural high-ceiling ventilation under a cool golpata thatch roof. For a full digital detox in nature.',
                'amenities' => ['High Speed Ceiling Fan', 'Free WiFi', 'Direct Riverview', 'Balcony Swing'],
                'base_price' => 5500,
                'max_guests' => 3,
                'image_path' => '/images/hero_bg.webp',
                'rooms' => ['E-201', 'E-202', 'E-203', 'E-204'],
            ],
            [
                'name' => 'Family Eco Grand Suite',
                'slug' => 'family-eco-grand-suite',
                'category' => RoomCategory::Ac,
                'vibe_tag' => 'family',
                'tagline' => 'Spacious Family Haven',
                'description' => 'Two interconnecting timber-panel bedrooms, split AC, coffee maker and an elevated terrace over the eco gardens and river.',
                'amenities' => ['Split AC', 'WiFi', 'Coffee Station', 'Up to 4-5 Guests'],
                'base_price' => 9000,
                'max_guests' => 5,
                'image_path' => '/images/dining.webp',
                'rooms' => ['F-301', 'F-302'],
            ],
        ];
    }

    /**
     * Winter high season for the Riverview suites: 20 Dec to 5 Jan.
     */
    private function seedWinterRate(): void
    {
        $year = now()->month === 1 ? now()->year - 1 : now()->year;

        RoomType::where('slug', 'premium-riverview-ac-suite')->first()
            ->rates()
            ->updateOrCreate(
                ['name' => 'Winter peak'],
                [
                    'starts_on' => "{$year}-12-20",
                    'ends_on' => ($year + 1).'-01-05',
                    'price_per_night' => 8500,
                ],
            );
    }
}
