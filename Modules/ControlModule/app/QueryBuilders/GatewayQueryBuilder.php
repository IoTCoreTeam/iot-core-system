<?php

namespace Modules\ControlModule\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\ControlModule\Models\Gateway;

class GatewayQueryBuilder
{
    public static function fromRequest(Request $request)
    {
        $perPage = $request->integer('per_page', 5);
        $query = self::buildQuery($request);

        return $query->paginate($perPage);
    }

    public static function buildQuery(Request $request): Builder
    {
        $query = Gateway::query();

        if ($request->has('external_id')) {
            return $query->where('external_id', $request->query('external_id'));
        }

        if ($request->has('name')) {
            return $query->where('name', $request->query('name'));
        }

        if ($request->has('ip_address')) {
            return $query->where('ip_address', $request->query('ip_address'));
        }

        if ($request->has('mac_address')) {
            return $query->where('mac_address', $request->query('mac_address'));
        }

        if ($request->has('search')) {
            $keyword = $request->query('search');

            return self::applySearch($query, $keyword);
        }

        return $query;
    }

    private static function applySearch(Builder $query, string $keyword): Builder
    {
        return $query->where('name', 'like', "%{$keyword}%")
            ->orWhere('external_id', 'like', "%{$keyword}%")
            ->orWhere('mac_address', 'like', "%{$keyword}%")
            ->orWhere('ip_address', 'like', "%{$keyword}%");
    }
}
