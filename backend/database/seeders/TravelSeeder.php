<?php

namespace Database\Seeders;

use App\Models\Travel;
use Illuminate\Database\Seeder;
use JsonException;

class TravelSeeder extends Seeder
{
    /**
     * @throws JsonException
     */
    public function run(): void
    {
        $path = base_path('../samples/travels.json');

        if (! is_file($path)) {
            return;
        }

        $raw = file_get_contents($path);

        if ($raw === false) {
            return;
        }

        $rows = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $now = now();

        $travels = array_map(static function (array $travel) use ($now): array {
            return [
                'id' => $travel['id'],
                'slug' => $travel['slug'],
                'name' => $travel['name'],
                'description' => $travel['description'],
                'starting_date' => $travel['startingDate'],
                'ending_date' => $travel['endingDate'],
                'price' => $travel['price'],
                'moods' => json_encode($travel['moods'], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $rows);

        Travel::query()->upsert(
            $travels,
            ['id'],
            ['slug', 'name', 'description', 'starting_date', 'ending_date', 'price', 'moods', 'updated_at']
        );
    }
}
