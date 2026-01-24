<?php

namespace Modules\ControlModule\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ControlModule\Models\NodeSensor;
use Modules\ControlModule\Models\Node;

class NodeSensorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = NodeSensor::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'node_id' => Node::factory(),
            'external_id' => 'sensor-' . $this->faker->unique()->randomNumber(5),
            'name' => $this->faker->words(2, true) . ' Sensor',
            'sensor_type' => $this->faker->randomElement(['dht11', 'ldr', 'rain', 'soil']),
            'last_reading' => $this->faker->randomFloat(4, 0, 100),
            'limit_value' => $this->faker->randomFloat(4, 80, 100),
            'registration_status' => 'registered',
        ];
    }
}
