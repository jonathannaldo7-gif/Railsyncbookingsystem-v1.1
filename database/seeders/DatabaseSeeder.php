<?php

namespace Database\Seeders;

use App\Models\Seat;
use App\Models\Train;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        User::firstOrCreate([
            'email' => 'passenger@example.com',
        ], [
            'name' => 'Passenger User',
            'password' => bcrypt('passenger123'),
            'role' => 'passenger',
        ]);

        $trains = [
            [
                'name' => 'Argo Parahyangan',
                'route' => 'Bandung - Gambir',
                'departure_time' => '2026-05-10 06:00:00',
                'arrival_time' => '2026-05-10 09:00:00',
                'capacity' => 10,
            ],
            [
                'name' => 'Taksaka',
                'route' => 'Yogyakarta - Gambir',
                'departure_time' => '2026-05-10 08:30:00',
                'arrival_time' => '2026-05-10 15:30:00',
                'capacity' => 10,
            ],
            [
                'name' => 'Bima',
                'route' => 'Surabaya Gubeng - Gambir',
                'departure_time' => '2026-05-10 10:00:00',
                'arrival_time' => '2026-05-10 22:30:00',
                'capacity' => 10,
            ],
            [
                'name' => 'Gajayana',
                'route' => 'Malang - Gambir',
                'departure_time' => '2026-05-10 14:00:00',
                'arrival_time' => '2026-05-11 06:00:00',
                'capacity' => 10,
            ],
            [
                'name' => 'Sriwijaya',
                'route' => 'Jakarta Kota - Banyuwangi',
                'departure_time' => '2026-05-10 12:00:00',
                'arrival_time' => '2026-05-11 02:30:00',
                'capacity' => 10,
            ],
        ];

        foreach ($trains as $trainData) {
            $train = Train::updateOrCreate([
                'name' => $trainData['name'],
                'route' => $trainData['route'],
            ], $trainData);

            $existingSeatCount = $train->seats()->count();
            if ($existingSeatCount < $trainData['capacity']) {
                $columns = 6;
                for ($index = $existingSeatCount; $index < $trainData['capacity']; $index++) {
                    $row = (int) floor($index / $columns);
                    $col = ($index % $columns) + 1;
                    $letter = '';
                    $current = $row;
                    while ($current >= 0) {
                        $letter = chr(65 + ($current % 26)) . $letter;
                        $current = (int) floor($current / 26) - 1;
                    }
                    $seatNumber = $letter . $col;
                    Seat::firstOrCreate([
                        'train_id' => $train->id,
                        'seat_number' => $seatNumber,
                    ], [
                        'is_reserved' => false,
                    ]);
                }
            }
        }
    }
}
