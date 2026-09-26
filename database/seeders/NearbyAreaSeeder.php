<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class NearbyAreaSeeder extends Seeder
{
    /**
     * Seed nearby suburbs around Mobil Riccarton (33 Riccarton Rd, Christchurch).
     */
    public function run(): void
    {
        // Remove dummy/test areas like ahmedabad
        Area::where('name', 'like', '%ahmedabad%')->delete();

        $nearbySuburbs = [
            [
                'name' => 'Riccarton',
                'latitude' => -43.5309,
                'longitude' => 172.6074,
                'display_name' => 'Riccarton, Christchurch, Canterbury, 8011, New Zealand',
            ],
            [
                'name' => 'Fendalton',
                'latitude' => -43.5132,
                'longitude' => 172.6015,
                'display_name' => 'Fendalton, Christchurch, Canterbury, 8052, New Zealand',
            ],
            [
                'name' => 'Christchurch Central (CBD)',
                'latitude' => -43.5321,
                'longitude' => 172.6362,
                'display_name' => 'Christchurch Central City, Christchurch, Canterbury, 8011, New Zealand',
            ],
            [
                'name' => 'Addington',
                'latitude' => -43.5412,
                'longitude' => 172.6105,
                'display_name' => 'Addington, Christchurch, Canterbury, 8024, New Zealand',
            ],
            [
                'name' => 'Upper Riccarton',
                'latitude' => -43.5304,
                'longitude' => 172.5765,
                'display_name' => 'Upper Riccarton, Christchurch, Canterbury, 8041, New Zealand',
            ],
            [
                'name' => 'Ilam',
                'latitude' => -43.5245,
                'longitude' => 172.5804,
                'display_name' => 'Ilam, Christchurch, Canterbury, 8041, New Zealand',
            ],
            [
                'name' => 'Merivale',
                'latitude' => -43.5115,
                'longitude' => 172.6231,
                'display_name' => 'Merivale, Christchurch, Canterbury, 8014, New Zealand',
            ],
            [
                'name' => 'Burnside',
                'latitude' => -43.4988,
                'longitude' => 172.5739,
                'display_name' => 'Burnside, Christchurch, Canterbury, 8053, New Zealand',
            ],
            [
                'name' => 'Spreydon',
                'latitude' => -43.5532,
                'longitude' => 172.6095,
                'display_name' => 'Spreydon, Christchurch, Canterbury, 8024, New Zealand',
            ],
            [
                'name' => 'Sydenham',
                'latitude' => -43.5489,
                'longitude' => 172.6355,
                'display_name' => 'Sydenham, Christchurch, Canterbury, 8023, New Zealand',
            ],
            [
                'name' => 'Papanui',
                'latitude' => -43.4927,
                'longitude' => 172.6102,
                'display_name' => 'Papanui, Christchurch, Canterbury, 8053, New Zealand',
            ],
            [
                'name' => 'Hornby',
                'latitude' => -43.5432,
                'longitude' => 172.5298,
                'display_name' => 'Hornby, Christchurch, Canterbury, 8042, New Zealand',
            ],
        ];

        foreach ($nearbySuburbs as $suburb) {
            Area::updateOrCreate(
                ['name' => $suburb['name']],
                [
                    'latitude' => $suburb['latitude'],
                    'longitude' => $suburb['longitude'],
                    'display_name' => $suburb['display_name'],
                ]
            );
        }
    }
}
