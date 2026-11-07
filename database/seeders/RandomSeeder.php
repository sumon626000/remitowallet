<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RandomSeeder extends Seeder
{


    public function run(): void
    {
        $this->seedTableWithRandomLocationData('user_logins');
    }

    private function seedTableWithRandomLocationData(string $table): void
    {
        $faker = Factory::create();
        $batchSize = 1000;

        // Location data for specific countries
        $locations = [
            'US' => ['city' => 'New York', 'lat' => 40.7128, 'lng' => -74.0060, 'country' => 'United States'],
            'CA' => ['city' => 'Toronto', 'lat' => 43.65107, 'lng' => -79.347015, 'country' => 'Canada'],
            'GB' => ['city' => 'London', 'lat' => 51.5074, 'lng' => -0.1278, 'country' => 'United Kingdom'],
            'AU' => ['city' => 'Sydney', 'lat' => -33.8688, 'lng' => 151.2093, 'country' => 'Australia'],
            'IN' => ['city' => 'Delhi', 'lat' => 28.7041, 'lng' => 77.1025, 'country' => 'India'],
        ];

        DB::table($table)->orderBy('id')->chunkById($batchSize, function ($rows) use ($faker, $table, $locations) {
            foreach ($rows as $row) {
                $countryCode = $faker->randomElement(array_keys($locations));
                $location = $locations[$countryCode];

                DB::table($table)
                    ->where('id', $row->id)
                    ->update([
                        'longitude' => $location['lng'],
                        'latitude' => $location['lat'],
                        'country_code' => $countryCode,
                        'location' => $location['city'],
                        'country' => $location['country'],
                        'ip_address' => $faker->ipv4,
                        'browser' => $faker->randomElement(['Chrome', 'Firefox', 'Edge', 'Safari', 'Opera']),
                        'os' => $faker->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
                        'get_device' => $faker->randomElement(['Desktop', 'Mobile', 'Tablet']),
                        'updated_at' => now(),
                    ]);
            }
        });

        $this->command->info("Table {$table} updated with valid location data based on country codes.");
    }










    private function seedTableWithRandomDates(string $table, string $startDate, string $endDate): void
    {
        $faker = Factory::create();
        $batchSize = 1000;

        DB::table($table)->orderBy('id')->chunk($batchSize, function ($rows) use ($faker, $startDate, $endDate, $table) {
            $updates = [];
            foreach ($rows as $row) {
                $updates[] = [
                    'id' => $row->id,
                    'created_at' => $faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s'),
                    'updated_at' => $faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s'),
                ];
            }
            // Bulk update with a single query
            DB::table($table)->upsert($updates, ['id'], ['created_at', 'updated_at']);
        });

        $this->command->info("Table {$table} seeded with random date data between {$startDate} and {$endDate}.");
    }


    private function seedTableWithRandomColumn(string $table, string $column): void
    {
        $faker = Factory::create();
        $batchSize = 1000;

        DB::table($table)->orderBy('id')->chunk($batchSize, function ($rows) use ($column, $faker, $table) {
            $updates = [];
            foreach ($rows as $row) {
                //$randomValue = $faker->numberBetween(1, 5);
                $randomValue = $faker->randomElement([3,5]);
                $updates[] = [
                    'id' => $row->id,
                    $column => $randomValue,
                ];
            }
            DB::table($table)->upsert($updates, ['id'], [$column]);
        });

        $this->command->info("Table {$table} column {$column} seeded with random values.");
    }


}
