<?php

namespace Modules\MapModule\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\MapModule\Models\Map;

class MapQueryBuilder
{
    public static function fromRequest(Request $request)
    {
        $perPage = $request->integer('per_page', 15);
        $query = self::buildQuery($request);

        return $query->paginate($perPage);
    }

    public static function buildQuery(Request $request): Builder
    {
        $query = Map::query()
            ->with('area')
            ->latest();

        if ($request->has('id')) {
            $query->where('id', $request->query('id'));
        }

        if ($request->filled('name')) {
            $query->where('name', $request->query('name'));
        }

        if ($request->has('area_id')) {
            $query->where('area_id', $request->query('area_id'));
        }

        if ($request->filled('image_url')) {
            $query->where('image_url', $request->query('image_url'));
        }

        if ($request->has('width_px')) {
            $query->where('width_px', $request->query('width_px'));
        }

        if ($request->has('height_px')) {
            $query->where('height_px', $request->query('height_px'));
        }

        if ($request->has('scale_m_per_px')) {
            $query->where('scale_m_per_px', $request->query('scale_m_per_px'));
        }

        if ($request->filled('description')) {
            $query->where('description', $request->query('description'));
        }

        if ($request->filled('keyword')) {
            self::applyKeyword($query, $request->query('keyword'));
        }

        if ($request->filled('start') || $request->filled('end')) {
            self::applyTime($query, $request->query('start'), $request->query('end'));
        }

        return $query;
    }

    private static function applyKeyword(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $sub) use ($keyword) {
            $sub->where('name', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('image_url', 'like', "%{$keyword}%");
        });
    }

    private static function applyTime(Builder $query, ?string $start, ?string $end): Builder
    {
        if ($start && $end) {
            return $query->whereBetween('created_at', [$start, $end]);
        }

        if ($start) {
            $query->where('created_at', '>=', $start);
        }

        if ($end) {
            $query->where('created_at', '<=', $end);
        }

        return $query;
    }
}
