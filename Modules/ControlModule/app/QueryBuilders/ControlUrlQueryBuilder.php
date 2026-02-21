<?php

namespace Modules\ControlModule\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\ControlModule\Models\ControlUrl;

class ControlUrlQueryBuilder
{
    public static function fromRequest(Request $request)
    {
        $perPage = $request->integer('per_page', 10);
        $query = self::buildQuery($request);

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public static function buildQuery(Request $request): Builder
    {
        $query = ControlUrl::query();

        $includes = collect(explode(',', (string) $request->query('include', '')))
            ->map(fn ($include) => trim((string) $include))
            ->filter()
            ->unique()
            ->values();

        if ($includes->contains('gateway')) {
            $query->with('node.gateway');
        } elseif ($includes->contains('node')) {
            $query->with('node');
        }

        if ($request->has('name')) {
            $query->where('name', $request->query('name'));
        }

        if ($request->has('input_type')) {
            $query->where('input_type', $request->query('input_type'));
        }

        if ($request->has('node_id')) {
            $query->where('node_id', $request->query('node_id'));
        }

        if ($request->has('search')) {
            $keyword = (string) $request->query('search');
            $query->where(function ($searchQuery) use ($keyword) {
                $searchQuery->where('name', 'like', "%{$keyword}%")
                    ->orWhere('url', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }
}
