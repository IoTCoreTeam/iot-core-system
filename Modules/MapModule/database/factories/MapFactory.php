<?php

namespace Modules\MapModule\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MapModule\Models\Area;
use Modules\MapModule\Models\Map;

class MapFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Map::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $width = $this->faker->numberBetween(600, 2000);
        $height = $this->faker->numberBetween(400, 1400);

        return [
            'area_id' => Area::factory(),
            'name' => $this->faker->unique()->words(3, true),
            'image_url' => $this->faker->optional()->imageUrl(1200, 800, 'technics', true),
            'width_px' => $width,
            'height_px' => $height,
            'scale_m_per_px' => $this->faker->randomFloat(6, 0.001, 0.05),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
