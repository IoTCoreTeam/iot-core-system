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
            'firmware_version' => $this->faker->semver(),
            'control_url' => $this->faker->url(),
        ];
    }
}
