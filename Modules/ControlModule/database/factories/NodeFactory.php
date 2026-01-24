<?php

namespace Modules\ControlModule\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ControlModule\Models\Node;
use Modules\ControlModule\Models\Gateway;

class NodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Node::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'gateway_id' => Gateway::factory(),
            'external_id' => 'node-' . $this->faker->unique()->randomNumber(5),
            'name' => $this->faker->words(2, true) . ' Node',
            'location' => $this->faker->secondaryAddress(),
            'registration_status' => 'registered',
            'description' => $this->faker->sentence(),
            'metadata' => [
                'hardware_revision' => '1.0',
                'chip_id' => $this->faker->uuid(),
            ],
        ];
    }
}
