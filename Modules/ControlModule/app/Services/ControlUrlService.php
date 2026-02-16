<?php

namespace Modules\ControlModule\Services;

use Illuminate\Support\Facades\DB;
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
}
