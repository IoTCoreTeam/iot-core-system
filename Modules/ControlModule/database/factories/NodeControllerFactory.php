<?php

namespace Modules\ControlModule\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ControlModule\Models\NodeController;
use Modules\ControlModule\Models\Node;

class NodeControllerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = NodeController::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'node_id' => Node::factory(),
            'external_id' => 'ctrl-' . $this->faker->unique()->randomNumber(5),
            'name' => $this->faker->words(2, true) . ' Controller',
            'firmware_version' => $this->faker->semver(),
            'registration_status' => 'registered',
        ];
    }
}
