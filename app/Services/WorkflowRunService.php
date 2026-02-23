<?php

namespace App\Services;

use App\Models\Workflow;
use Illuminate\Support\Facades\Http;
use Modules\ControlModule\Models\ControlUrl;
use Modules\ControlModule\Services\ControlUrlService;

class WorkflowRunService
{
    private ControlUrlService $controlUrlService;

    public function __construct(ControlUrlService $controlUrlService)
    {
        $this->controlUrlService = $controlUrlService;
    }

    /**
     * @return array<string, mixed>
     */
    public function run(Workflow $workflow): array
    {
        $definition = $workflow->control_definition ?? $workflow->definition ?? null;
        if (! is_array($definition) || empty($definition['nodes'])) {
            throw new \RuntimeException('Workflow definition is empty.');
        }

        $nodes = $definition['nodes'] ?? [];
        $edges = $definition['edges'] ?? [];

        $deviceStatus = $this->fetchDeviceStatus();
        $this->assertDevicesOnline($nodes, $deviceStatus);
        $this->ensureAllDevicesOff();

        try {
            $result = $this->executeFlow($nodes, $edges);
            return [
                'workflow_id' => $workflow->id,
                'status' => 'completed',
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            $this->abortAllDevices();
            throw $e;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchDeviceStatus(): array
    {
        $baseUrl = rtrim((string) config('services.node_server.base_url'), '/');
        $response = Http::timeout(10)->get($baseUrl . '/v1/device-status');

        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch device status.');
        }

        $payload = $response->json();
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        if (is_array($payload)) {
            return $payload;
        }

        return [];
    }

    /**
     * @param array<int, mixed> $nodes
     * @param array<int, mixed> $deviceStatus
     */
    private function assertDevicesOnline(array $nodes, array $deviceStatus): void
    {
        $requiredNodes = $this->collectRequiredNodes($nodes);
        if (empty($requiredNodes)) {
            return;
        }

        $onlineNodes = $this->indexOnlineNodes($deviceStatus);

        foreach ($requiredNodes as $key => $label) {
            if (! isset($onlineNodes[$key])) {
                throw new \RuntimeException("Device is offline or missing: {$label}");
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function collectRequiredNodes(array $nodes): array
    {
        $required = [];
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }
            if (($node['type'] ?? null) !== 'action') {
                continue;
            }
            $controlUrlId = $node['control_url_id'] ?? null;
            if (! $controlUrlId) {
                continue;
            }
            $controlUrl = ControlUrl::with('node.gateway')->find($controlUrlId);
            if (! $controlUrl) {
                continue;
            }
            $gatewayExternalId = $controlUrl->node?->gateway?->external_id;
            $nodeExternalId = $controlUrl->node?->external_id;
            if (! $gatewayExternalId || ! $nodeExternalId) {
                continue;
            }
            $key = $gatewayExternalId . '::' . $nodeExternalId;
            $required[$key] = "{$gatewayExternalId} / {$nodeExternalId}";
        }

        return $required;
    }

    /**
     * @param array<int, mixed> $deviceStatus
     * @return array<string, bool>
     */
    private function indexOnlineNodes(array $deviceStatus): array
    {
        $online = [];
        foreach ($deviceStatus as $gateway) {
            if (! is_array($gateway)) {
                continue;
            }
            $gatewayId = $gateway['id'] ?? $gateway['gateway_id'] ?? null;
            if (! $gatewayId) {
                continue;
            }
            $gatewayStatus = strtolower((string) ($gateway['status'] ?? ''));
            if ($gatewayStatus !== 'online') {
                continue;
            }
            $nodes = $gateway['nodes'] ?? [];
            if (! is_array($nodes)) {
                continue;
            }
            foreach ($nodes as $node) {
                if (! is_array($node)) {
                    continue;
                }
                $nodeId = $node['id'] ?? $node['node_id'] ?? null;
                if (! $nodeId) {
                    continue;
                }
                $nodeStatus = strtolower((string) ($node['status'] ?? ''));
                if ($nodeStatus !== 'online') {
                    continue;
                }
                $key = $gatewayId . '::' . $nodeId;
                $online[$key] = true;
            }
        }

        return $online;
    }

    private function ensureAllDevicesOff(): void
    {
        $baseUrl = rtrim((string) config('services.node_server.base_url'), '/');
        $response = Http::timeout(15)->post($baseUrl . '/v1/device-status/ensure-off');

        if ($response->failed()) {
            throw new \RuntimeException('Failed to turn off devices.');
        }

        $payload = $response->json();
        if (isset($payload['success']) && $payload['success'] === false) {
            throw new \RuntimeException('Failed to turn off devices.');
        }
    }

    /**
     * @param array<int, mixed> $nodes
     * @param array<int, mixed> $edges
     * @return array<string, mixed>
     */
    private function executeFlow(array $nodes, array $edges): array
    {
        $nodeMap = $this->indexNodes($nodes);
        $edgeMap = $this->indexEdges($edges);
        $startId = $this->findNodeIdByType($nodes, 'start');
        $endId = $this->findNodeIdByType($nodes, 'end');

        if (! $startId || ! $endId) {
            throw new \RuntimeException('Workflow must contain start and end nodes.');
        }

        $currentId = $startId;
        $visited = [];
        $steps = 0;
        $maxSteps = max(count($nodes) * 5, 20);

        while ($currentId) {
            if ($steps > $maxSteps) {
                throw new \RuntimeException('Workflow exceeded maximum steps.');
            }
            $steps++;

            if ($currentId === $endId) {
                return [
                    'visited' => $visited,
                    'steps' => $steps,
                ];
            }

            $node = $nodeMap[$currentId] ?? null;
            if (! $node) {
                throw new \RuntimeException("Node not found: {$currentId}");
            }
            $visited[] = $currentId;

            $type = $node['type'] ?? null;
            if ($type === 'action') {
                $this->runActionNode($node);
                $currentId = $this->resolveNextNodeId($currentId, $edgeMap, null);
                continue;
            }

            if ($type === 'condition') {
                $result = $this->evaluateConditionNode($node);
                $branch = $result ? 'true' : 'false';
                $currentId = $this->resolveNextNodeId($currentId, $edgeMap, $branch);
                continue;
            }

            $currentId = $this->resolveNextNodeId($currentId, $edgeMap, null);
        }

        throw new \RuntimeException('Workflow ended unexpectedly.');
    }

    /**
     * @param array<int, mixed> $nodes
     * @return array<string, mixed>
     */
    private function indexNodes(array $nodes): array
    {
        $map = [];
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }
            $id = $node['id'] ?? null;
            if (! $id) {
                continue;
            }
            $map[$id] = $node;
        }
        return $map;
    }

    /**
     * @param array<int, mixed> $edges
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function indexEdges(array $edges): array
    {
        $map = [];
        foreach ($edges as $edge) {
            if (! is_array($edge)) {
                continue;
            }
            $source = $edge['source'] ?? null;
            if (! $source) {
                continue;
            }
            $map[$source][] = $edge;
        }
        return $map;
    }

    /**
     * @param array<int, mixed> $nodes
     */
    private function findNodeIdByType(array $nodes, string $type): ?string
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }
            if (($node['type'] ?? null) === $type) {
                return $node['id'] ?? null;
            }
        }
        return null;
    }

    /**
     * @param array<string, array<int, array<string, mixed>>> $edgeMap
     */
    private function resolveNextNodeId(string $currentId, array $edgeMap, ?string $branch): ?string
    {
        $edges = $edgeMap[$currentId] ?? [];
        if (empty($edges)) {
            return null;
        }
        if (! $branch) {
            $edge = $edges[0] ?? null;
            return $edge['target'] ?? null;
        }
        foreach ($edges as $edge) {
            $edgeBranch = $edge['branch'] ?? null;
            if ($edgeBranch === $branch) {
                return $edge['target'] ?? null;
            }
        }
        return null;
    }

    /**
     * @param array<string, mixed> $node
     */
    private function runActionNode(array $node): void
    {
        $controlUrlId = $node['control_url_id'] ?? null;
        if (! $controlUrlId) {
            throw new \RuntimeException('Action node missing control_url_id.');
        }
        $duration = (int) ($node['duration_seconds'] ?? 0);

        $this->controlUrlService->execute($controlUrlId, [
            'state' => 'on',
        ]);

        if ($duration > 0) {
            sleep($duration);
        }

        $this->controlUrlService->execute($controlUrlId, [
            'state' => 'off',
        ]);
    }

    /**
     * @param array<string, mixed> $node
     */
    private function evaluateConditionNode(array $node): bool
    {
        $metricKey = $node['metric_key'] ?? null;
        $operator = $node['operator'] ?? '>';
        $value = $node['value'] ?? null;
        if (! $metricKey || $value === null) {
            throw new \RuntimeException('Condition node missing metric data.');
        }

        $latest = $this->fetchLatestMetricValue((string) $metricKey);
        if ($latest === null) {
            throw new \RuntimeException('Failed to evaluate condition.');
        }

        $threshold = (float) $value;
        $current = (float) $latest;

        return match ($operator) {
            '>' => $current > $threshold,
            '<' => $current < $threshold,
            '>=' => $current >= $threshold,
            '<=' => $current <= $threshold,
            '==' => $current == $threshold,
            '!=' => $current != $threshold,
            default => $current > $threshold,
        };
    }

    private function fetchLatestMetricValue(string $metricKey): ?float
    {
        $baseUrl = rtrim((string) config('services.node_server.base_url'), '/');
        $mapped = $this->mapMetricKey($metricKey);
        $query = http_build_query([
            'sensor_type' => $mapped,
            'limit' => 1,
            'page' => 1,
        ]);

        $response = Http::timeout(10)->get($baseUrl . '/v1/sensors/query?' . $query);
        if ($response->failed()) {
            return null;
        }

        $payload = $response->json();
        if (! is_array($payload) || empty($payload[0])) {
            return null;
        }

        $row = $payload[0];
        $value = $row['value'] ?? ($row['_id']['value'] ?? null);
        if ($value === null) {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function mapMetricKey(string $metricKey): string
    {
        return match ($metricKey) {
            'soilMoisture' => 'soil',
            'soil_moisture' => 'soil',
            'airHumidity' => 'humidity',
            'air_humidity' => 'humidity',
            default => $metricKey,
        };
    }

    private function abortAllDevices(): void
    {
        try {
            $this->ensureAllDevicesOff();
        } catch (\Throwable $e) {
            // ignore abort errors
        }
    }
}
