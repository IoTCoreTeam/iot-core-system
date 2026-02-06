<?php

namespace Modules\MapModule\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MapModule\Models\Area;

class AreaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Area::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->optional()->sentence(),
            'height_m' => $this->faker->randomFloat(2, 2.5, 6.5),
        ];
    }
}
