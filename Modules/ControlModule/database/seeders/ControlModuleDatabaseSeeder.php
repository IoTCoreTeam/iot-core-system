<?php

namespace Modules\ControlModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ControlModule\Models\Gateway;
use Modules\ControlModule\Models\Node;
use Modules\ControlModule\Models\NodeSensor;

class ControlModuleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateway = Gateway::factory()->create([
            'external_id' => 'GW_001',
            'name' => 'My Test Gateway',
            'registration_status' => true,
        ]);

        $node = Node::factory()->create([
            'gateway_id' => $gateway->id,
            'external_id' => 'node-001',
            'name' => 'Environmental Node',
            'registration_status' => 'registered',
        ]);

        $sensors = [
            ['sensor_type' => 'dht11', 'external_id' => 'dht11-001', 'name' => 'DHT11 Sensor 001'],
            ['sensor_type' => 'ldr', 'external_id' => 'ldr-001', 'name' => 'LDR Sensor 001'],
            ['sensor_type' => 'rain', 'external_id' => 'rain-001', 'name' => 'Rain Sensor 001'],
            ['sensor_type' => 'soil', 'external_id' => 'soil-001', 'name' => 'Soil Sensor 001'],
            ['sensor_type' => 'dht11', 'external_id' => 'dht11-002', 'name' => 'DHT11 Sensor 002'],
            ['sensor_type' => 'ldr', 'external_id' => 'ldr-002', 'name' => 'LDR Sensor 002'],
            ['sensor_type' => 'rain', 'external_id' => 'rain-002', 'name' => 'Rain Sensor 002'],
            ['sensor_type' => 'soil', 'external_id' => 'soil-002', 'name' => 'Soil Sensor 002'],
        ];

        foreach ($sensors as $sensor) {
            NodeSensor::factory()->create([
                'node_id' => $node->id,
                'sensor_type' => $sensor['sensor_type'],
                'external_id' => $sensor['external_id'],
                'name' => $sensor['name'],
                'registration_status' => 'registered',
            ]);
        }
    }
}
