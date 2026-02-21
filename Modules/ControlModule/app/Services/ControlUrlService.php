<?php

namespace Modules\ControlModule\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\ControlModule\Helpers\SystemLogHelper;
use Modules\ControlModule\Models\ControlUrl;

class ControlUrlService
{
    /**
     * @param array<string, mixed> $payload
     * @return array{control_url: ControlUrl, message: string, status: int}
     */
    public function create(array $payload): array
    {
        $controlUrl = DB::transaction(function () use ($payload) {
            return $this->upsertByControllerId($payload);
        });

        SystemLogHelper::log('control_url.created', 'Control url created successfully', [
            'control_url_id' => $controlUrl->id,
        ]);

        return [
            'control_url' => $controlUrl->refresh(),
            'message' => 'Control url created successfully',
            'status' => 201,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{control_url: ControlUrl, message: string}
     */
    public function update(string $id, array $payload): array
    {
        $controlUrl = DB::transaction(function () use ($id, $payload) {
            if (! empty($payload['controller_id'])) {
                return $this->upsertByControllerId($payload);
            }
            $controlUrl = ControlUrl::findOrFail($id);
            $controlUrl->update($payload);
            return $controlUrl;
        });

        SystemLogHelper::log('control_url.updated', 'Control url updated successfully', [
            'control_url_id' => $controlUrl->id,
        ]);

        return [
            'control_url' => $controlUrl->refresh(),
            'message' => 'Control url updated successfully',
        ];
    }

    public function delete(string $id): void
    {
        DB::transaction(function () use ($id) {
            $controlUrl = ControlUrl::where('id', $id)
                ->orWhere('controller_id', $id)
                ->firstOrFail();
            $controlUrl->delete();
        });

        SystemLogHelper::log('control_url.deleted', 'Control url deleted successfully', [
            'control_url_id' => $id,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{control_url: ControlUrl, message: string, status: int, response: mixed}
     */
    public function execute(string $id, array $payload): array
    {
        return DB::transaction(function () use ($id, $payload) {
            $controlUrl = ControlUrl::with('node.gateway')->findOrFail($id);

            $endpoint = $this->resolveEndpoint($controlUrl, $payload);
            $commandPayload = $this->buildCommandPayload($controlUrl, $payload);

            SystemLogHelper::log('control_url.execute_started', 'Executing control url', [
                'control_url_id' => $controlUrl->id,
                'endpoint' => $endpoint,
                'payload' => $commandPayload,
            ]);

            $response = Http::timeout(10)->post($endpoint, $commandPayload);

            if ($response->failed()) {
                SystemLogHelper::log('control_url.execute_failed', 'Control url execution failed', [
                    'control_url_id' => $controlUrl->id,
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $response->json() ?? $response->body(),
                ], ['level' => 'error']);

                throw new \RuntimeException('Failed to execute control url');
            }

            SystemLogHelper::log('control_url.executed', 'Control url executed successfully', [
                'control_url_id' => $controlUrl->id,
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);

            return [
                'control_url' => $controlUrl->refresh(),
                'message' => 'Control url executed successfully',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolveEndpoint(ControlUrl $controlUrl, array $payload): string
    {
        $url = (string) ($payload['url'] ?? $controlUrl->url);
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $baseUrl = rtrim((string) config('services.node_server.base_url'), '/');
        $relativeUrl = '/' . ltrim($url, '/');
        return $baseUrl . '/v1/control' . $relativeUrl;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function buildCommandPayload(ControlUrl $controlUrl, array $payload): array
    {
        $node = $controlUrl->node;
        $gatewayExternalId = $node?->gateway?->external_id;
        $nodeExternalId = $node?->external_id;

        $commandPayload = $payload;
        unset($commandPayload['url']);

        if (! empty($gatewayExternalId) && empty($commandPayload['gateway_id'])) {
            $commandPayload['gateway_id'] = $gatewayExternalId;
        }

        if (! empty($nodeExternalId) && empty($commandPayload['node_id'])) {
            $commandPayload['node_id'] = $nodeExternalId;
        }

        $commandPayload['requested_at'] = $commandPayload['requested_at'] ?? now()->toISOString();

        return $commandPayload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function upsertByControllerId(array $payload): ControlUrl
    {
        $controllerId = isset($payload['controller_id']) ? (string) $payload['controller_id'] : '';
        if ($controllerId === '') {
            return ControlUrl::create($payload);
        }

        $existing = ControlUrl::where('controller_id', $controllerId)->first();
        if ($existing) {
            $existing->update($payload);
            return $existing;
        }

        return ControlUrl::create($payload);
    }
}
