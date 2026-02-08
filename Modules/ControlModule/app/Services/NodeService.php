<?php

namespace Modules\ControlModule\Services;

use Modules\ControlModule\Helpers\SystemLogHelper;
use Modules\ControlModule\Models\Node;

class NodeService
{
    /**
     * @param  array<string,mixed>  $payload
     * @return array{
     *     node: Node,
     *     message: string,
     *     status: int
     * }
     */
    public function register(array $payload): array
    {
        $input = $payload;
        $payload = [
            'external_id' => $input['external_id'],
            'gateway_id' => $input['gateway_id'] ?? null,
            'name' => $input['name'] ?? null,
            'mac_address' => $input['mac_address'] ?? null,
            'ip_address' => $input['ip_address'] ?? null,
            'description' => $input['description'] ?? null,
            'metadata' => $input['metadata'] ?? null,
            'registration_status' => 'registered',
        ];

        if (array_key_exists('type', $input) && $input['type'] !== null) {
            $payload['type'] = $input['type'];
        }

        $node = Node::where('external_id', $payload['external_id'])->first();
        $created = false;

        if (! $node) {
            $node = Node::create($payload);
            $created = true;
        } else {
            $node->update($payload);
        }

        SystemLogHelper::log(
            'node.registered',
            'Node registered successfully',
            ['node_id' => $node->id]
        );

        return [
            'node' => $node->refresh(),
            'message' => $created ? 'Node registered successfully' : 'Node registration refreshed successfully',
            'status' => $created ? 201 : 200,
        ];
    }

    /**
     * @return array{node: Node, message: string}
     */
    public function deactivate(string $externalId): array
    {
        $node = Node::where('external_id', $externalId)->firstOrFail();
        $node->update(['registration_status' => 'pending']);

        SystemLogHelper::log('node.deactivated', 'Node deactivated successfully', ['node_id' => $node->id]);

        return [
            'node' => $node->refresh(),
            'message' => 'Node deactivated successfully',
        ];
    }
}
