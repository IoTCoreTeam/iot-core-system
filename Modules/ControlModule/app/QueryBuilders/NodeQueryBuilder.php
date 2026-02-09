<?php

namespace Modules\ControlModule\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\ControlModule\Models\Gateway;
use Modules\ControlModule\Models\Node;
use Modules\ControlModule\Models\NodeController;
use Modules\ControlModule\Models\NodeSensor;

class NodeQueryBuilder
{
    public static function fromRequest(Request $request)
    {
        $perPage = $request->integer('per_page', 5);
        $query = self::buildQuery($request);

        return $query->paginate($perPage);
    }

    public static function buildQuery(Request $request): Builder
    {
        $type = strtolower((string) $request->query('type', ''));
        $query = Node::query();

        if ($type === 'controller') {
            $query->whereHas('controllers');
            $query = self::applyNodeFilters($query, $request, false);

            return self::applyControllerRelationFilters($query, $request);
        }

        if ($type === 'sensor') {
            $query->whereHas('sensors');
            $query = self::applyNodeFilters($query, $request, false);

            return self::applySensorRelationFilters($query, $request);
        }

        return self::applyNodeFilters($query, $request);
    }

    /**
     * @return array{
     *     gateways: array<int, string>,
     *     nodes: array<int, string>,
     *     node_controllers: array<int, string>,
     *     node_sensors: array<int, string>
     * }
     */
    public static function getActiveDevicesPayload(): array
    {
        return [
            'gateways' => Gateway::where('registration_status', true)
                ->pluck('external_id')
                ->filter()
                ->values()
                ->all(),
            'nodes' => Node::where('registration_status', 'registered')
                ->pluck('external_id')
                ->filter()
                ->values()
                ->all(),
            'node_controllers' => NodeController::whereHas('node', function ($query) {
                $query->where('registration_status', 'registered');
            })->with('node:id,external_id')
                ->get()
                ->pluck('node.external_id')
                ->filter()
                ->values()
                ->all(),
            'node_sensors' => NodeSensor::whereHas('node', function ($query) {
                $query->where('registration_status', 'registered');
            })->with('node:id,external_id')
                ->get()
                ->pluck('node.external_id')
                ->filter()
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{
     *     gateways: array<int, string>,
     *     nodes: array<int, string>,
     *     node_controllers: array<int, mixed>,
     *     node_sensors: array<int, mixed>
     * }
     */
    public static function getWhitelistPayload(): array
    {
        return [
            'gateways' => Gateway::where('registration_status', true)
                ->pluck('external_id')
                ->filter()
                ->values()
                ->all(),
            'nodes' => Node::where('registration_status', 'registered')
                ->pluck('external_id')
                ->filter()
                ->values()
                ->all(),
            'node_controllers' => NodeController::whereHas('node', function ($query) {
                $query->where('registration_status', 'registered');
            })->with('node:id,external_id,name,registration_status')
                ->get()
                ->map(function (NodeController $controller) {
                    return array_merge($controller->toArray(), [
                        'external_id' => $controller->node?->external_id,
                        'name' => $controller->node?->name,
                        'registration_status' => $controller->node?->registration_status,
                    ]);
                })->values()->all(),
            'node_sensors' => NodeSensor::whereHas('node', function ($query) {
                $query->where('registration_status', 'registered');
            })->with('node:id,external_id,name,registration_status')
                ->get()
                ->map(function (NodeSensor $sensor) {
                    return array_merge($sensor->toArray(), [
                        'external_id' => $sensor->node?->external_id,
                        'name' => $sensor->node?->name,
                        'registration_status' => $sensor->node?->registration_status,
                    ]);
                })->values()->all(),
        ];
    }

    private static function applyNodeFilters(Builder $query, Request $request, bool $includeSearch = true): Builder
    {
        if ($request->has('external_id')) {
            return $query->where('external_id', $request->query('external_id'));
        }

        if ($request->has('name')) {
            return $query->where('name', $request->query('name'));
        }

        if ($request->has('gateway_id')) {
            return $query->where('gateway_id', $request->query('gateway_id'));
        }

        if ($request->has('registration_status')) {
            return $query->where('registration_status', $request->query('registration_status'));
        }

        if ($request->has('description')) {
            return $query->where('description', $request->query('description'));
        }

        if ($includeSearch && $request->has('search')) {
            $keyword = $request->query('search');

            return self::applyNodeSearch($query, $keyword);
        }

        return $query;
    }

    private static function applyControllerRelationFilters(Builder $query, Request $request): Builder
    {
        if ($request->has('node_id')) {
            return $query->whereHas('controllers', function (Builder $controllerQuery) use ($request) {
                $controllerQuery->where('node_id', $request->query('node_id'));
            });
        }

        if ($request->has('firmware_version')) {
            return $query->whereHas('controllers', function (Builder $controllerQuery) use ($request) {
                $controllerQuery->where('firmware_version', $request->query('firmware_version'));
            });
        }

        if ($request->has('control_url')) {
            return $query->whereHas('controllers', function (Builder $controllerQuery) use ($request) {
                $controllerQuery->where('control_url', $request->query('control_url'));
            });
        }

        if ($request->has('search')) {
            $keyword = $request->query('search');

            return $query->where(function (Builder $nodeQuery) use ($keyword) {
                $nodeQuery->where('name', 'like', "%{$keyword}%")
                    ->orWhere('external_id', 'like', "%{$keyword}%")
                    ->orWhere('registration_status', 'like', "%{$keyword}%")
                    ->orWhereHas('controllers', function (Builder $controllerQuery) use ($keyword) {
                        $controllerQuery->where('firmware_version', 'like', "%{$keyword}%")
                            ->orWhere('control_url', 'like', "%{$keyword}%");
                    });
            });
        }

        return $query;
    }

    private static function applySensorRelationFilters(Builder $query, Request $request): Builder
    {
        if ($request->has('node_id')) {
            return $query->whereHas('sensors', function (Builder $sensorQuery) use ($request) {
                $sensorQuery->where('node_id', $request->query('node_id'));
            });
        }

        if ($request->has('sensor_type')) {
            return $query->whereHas('sensors', function (Builder $sensorQuery) use ($request) {
                $sensorQuery->where('sensor_type', $request->query('sensor_type'));
            });
        }

        if ($request->has('last_reading')) {
            return $query->whereHas('sensors', function (Builder $sensorQuery) use ($request) {
                $sensorQuery->where('last_reading', $request->query('last_reading'));
            });
        }

        if ($request->has('limit_value')) {
            return $query->whereHas('sensors', function (Builder $sensorQuery) use ($request) {
                $sensorQuery->where('limit_value', $request->query('limit_value'));
            });
        }

        if ($request->has('search')) {
            $keyword = $request->query('search');

            return $query->where(function (Builder $nodeQuery) use ($keyword) {
                $nodeQuery->where('name', 'like', "%{$keyword}%")
                    ->orWhere('external_id', 'like', "%{$keyword}%")
                    ->orWhere('registration_status', 'like', "%{$keyword}%")
                    ->orWhereHas('sensors', function (Builder $sensorQuery) use ($keyword) {
                        $sensorQuery->where('sensor_type', 'like', "%{$keyword}%");
                    });
            });
        }

        return $query;
    }

    private static function applyNodeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where('name', 'like', "%{$keyword}%")
            ->orWhere('external_id', 'like', "%{$keyword}%")
            ->orWhere('registration_status', 'like', "%{$keyword}%");
    }

    private static function applyControllerSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $controllerQuery) use ($keyword) {
            $controllerQuery->where('firmware_version', 'like', "%{$keyword}%")
                ->orWhere('control_url', 'like', "%{$keyword}%")
                ->orWhereHas('node', function (Builder $nodeQuery) use ($keyword) {
                    $nodeQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('external_id', 'like', "%{$keyword}%")
                        ->orWhere('registration_status', 'like', "%{$keyword}%");
                });
        });
    }

    private static function applySensorSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $sensorQuery) use ($keyword) {
            $sensorQuery->where('sensor_type', 'like', "%{$keyword}%")
                ->orWhereHas('node', function (Builder $nodeQuery) use ($keyword) {
                    $nodeQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('external_id', 'like', "%{$keyword}%")
                        ->orWhere('registration_status', 'like', "%{$keyword}%");
                });
        });
    }
}
