<?php

namespace Modules\ControlModule\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ControlModule\Models\Gateway;

class GatewayFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Gateway::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name() . ' Gateway',
            'external_id' => 'GW_' . $this->faker->unique()->randomNumber(5),
            'connection_key' => $this->faker->unique()->uuid(),
            'location' => $this->faker->city(),
            'ip_address' => $this->faker->ipv4(),
            'description' => $this->faker->sentence(),
            'registration_status' => true,
        ];
    }
}
