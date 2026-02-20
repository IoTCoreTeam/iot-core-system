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
            return ControlUrl::create($payload);
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
            $controlUrl = ControlUrl::findOrFail($id);
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

            $status = $this->resolveStatus($payload);
            if ($status !== null) {$controlUrl->status = $status; $controlUrl->save();}

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
    private function resolveStatus(array $payload): ?string
    {
        $status = $payload['status'] ?? $payload['state'] ?? null;
        if (! is_string($status)) {
            return null;
        }

        $status = strtolower(trim($status));
        return in_array($status, ['on', 'off'], true) ? $status : null;
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
}
