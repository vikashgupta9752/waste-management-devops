<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bin;

class BinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bins = [
            [
                'location_name' => 'LPU Main Gate',
                'latitude' => 31.2559,
                'longitude' => 75.7051,
                'fill_level' => 45,
                'status' => 'active'
            ],
            [
                'location_name' => 'Law Gate Phagwara',
                'latitude' => 31.2480,
                'longitude' => 75.7010,
                'fill_level' => 85,
                'status' => 'active'
            ],
            [
                'location_name' => 'LPU Block 34',
                'latitude' => 31.2580,
                'longitude' => 75.7100,
                'fill_level' => 10,
                'status' => 'active'
            ],
            [
                'location_name' => 'Phagwara Bus Stand',
                'latitude' => 31.2220,
                'longitude' => 75.7720,
                'fill_level' => 60,
                'status' => 'active'
            ],
            [
                'location_name' => 'LPU Residential Area',
                'latitude' => 31.2600,
                'longitude' => 75.7150,
                'fill_level' => 95,
                'status' => 'full'
            ],
        ];

        foreach ($bins as $bin) {
            Bin::create($bin);
        }
    }
}
