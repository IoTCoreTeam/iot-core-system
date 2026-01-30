<?php

namespace App\QueryBuilders;

use App\Models\SystemLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SystemLogQueryBuilder
{
    public static function fromRequest(Request $request)
    {
        $perPage = $request->integer('per_page', 15);
        $query = self::buildQuery($request);

        return $query->paginate($perPage);
    }

    public static function buildQuery(Request $request): Builder
    {
        $query = SystemLog::query()->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('level')) {
            $query->where('level', $request->query('level'));
        }

        if ($request->filled('message')) {
            $query->where('message', $request->query('message'));
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', $request->query('ip_address'));
        }

        if ($request->filled('context')) {
            $query->where('context', $request->query('context'));
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
            $sub->where('message', 'like', "%{$keyword}%")
                ->orWhere('action', 'like', "%{$keyword}%")
                ->orWhere('context', 'like', "%{$keyword}%");
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
