<?php

namespace Modules\ControlModule\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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

        if ($type === 'controller') {
            $query = NodeController::query();

            return self::applyControllerFilters($query, $request);
        }

        if ($type === 'sensor') {
            $query = NodeSensor::query();

            return self::applySensorFilters($query, $request);
        }

        $query = Node::query();

        return self::applyNodeFilters($query, $request);
    }

    private static function applyNodeFilters(Builder $query, Request $request): Builder
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

        if ($request->has('metadata')) {
            return $query->where('metadata', $request->query('metadata'));
        }

        if ($request->has('search')) {
            $keyword = $request->query('search');

            return self::applyNodeSearch($query, $keyword);
        }

        return $query;
    }

    private static function applyControllerFilters(Builder $query, Request $request): Builder
    {
        if ($request->has('external_id')) {
            return $query->whereHas('node', function (Builder $nodeQuery) use ($request) {
                $nodeQuery->where('external_id', $request->query('external_id'));
            });
        }

        if ($request->has('name')) {
            return $query->whereHas('node', function (Builder $nodeQuery) use ($request) {
                $nodeQuery->where('name', $request->query('name'));
            });
        }

        if ($request->has('node_id')) {
            return $query->where('node_id', $request->query('node_id'));
        }

        if ($request->has('gateway_id')) {
            return $query->whereHas('node', function (Builder $nodeQuery) use ($request) {
                $nodeQuery->where('gateway_id', $request->query('gateway_id'));
            });
        }

        if ($request->has('firmware_version')) {
            return $query->where('firmware_version', $request->query('firmware_version'));
        }

        if ($request->has('control_url')) {
            return $query->where('control_url', $request->query('control_url'));
        }

        if ($request->has('registration_status')) {
            return $query->whereHas('node', function (Builder $nodeQuery) use ($request) {
                $nodeQuery->where('registration_status', $request->query('registration_status'));
            });
        }

        if ($request->has('search')) {
            $keyword = $request->query('search');

            return self::applyControllerSearch($query, $keyword);
        }

        return $query;
    }

    private static function applySensorFilters(Builder $query, Request $request): Builder
    {
        if ($request->has('external_id')) {
            return $query->whereHas('node', function (Builder $nodeQuery) use ($request) {
                $nodeQuery->where('external_id', $request->query('external_id'));
            });
        }

        if ($request->has('name')) {
            return $query->whereHas('node', function (Builder $nodeQuery) use ($request) {
                $nodeQuery->where('name', $request->query('name'));
            });
        }

        if ($request->has('node_id')) {
            return $query->where('node_id', $request->query('node_id'));
        }

        if ($request->has('gateway_id')) {
            return $query->whereHas('node', function (Builder $nodeQuery) use ($request) {
                $nodeQuery->where('gateway_id', $request->query('gateway_id'));
            });
        }

        if ($request->has('sensor_type')) {
            return $query->where('sensor_type', $request->query('sensor_type'));
        }

        if ($request->has('last_reading')) {
            return $query->where('last_reading', $request->query('last_reading'));
        }

        if ($request->has('limit_value')) {
            return $query->where('limit_value', $request->query('limit_value'));
        }

        if ($request->has('registration_status')) {
            return $query->whereHas('node', function (Builder $nodeQuery) use ($request) {
                $nodeQuery->where('registration_status', $request->query('registration_status'));
            });
        }

        if ($request->has('search')) {
            $keyword = $request->query('search');

            return self::applySensorSearch($query, $keyword);
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
