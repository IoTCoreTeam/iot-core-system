<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MapModule\Models\Area;
use Modules\MapModule\Models\Map;

class MapModuleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = Area::factory()->count(10)->create();

        Map::factory()->count(30)->create([
            'area_id' => $areas->random()->id,
        ]);
    }
}
