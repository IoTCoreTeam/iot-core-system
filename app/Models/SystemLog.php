<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class SystemLog extends Model
{
    /** @use HasFactory<\Database\Factories\SystemLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'level',
        'message',
        'context',
        'ip_address',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Build a normalized filter array that downstream scopes understand.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalizeFilters(array $input = []): array
    {
        $levels = $input['levels'] ?? [];

        if (! is_array($levels)) {
            $levels = [$levels];
        }

        return [
            'keyword' => $input['search'] ?? $input['keyword'] ?? null,
            'start' => $input['start'] ?? null,
            'end' => $input['end'] ?? null,
            'level' => $input['level'] ?? null,
            'levels' => $levels,
            'action' => $input['action'] ?? null,
            'user_id' => $input['user_id'] ?? null,
            'ip_address' => $input['ip_address'] ?? ($input['ip'] ?? null),
        ];
    }

    /**
     * Keep pagination bounds within safe defaults.
     */
    public static function normalizePerPage(?int $perPage, int $default = 15): int
    {
        $perPage = $perPage ?? $default;

        return max(1, min($perPage, 100));
    }

    /**
     * Fetch paginated logs with relationships using normalized filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public static function fetchForListing(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return static::query()
            ->with('user:id,name,email')
            ->applyFilters($filters)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Filter logs by a search keyword across action, level, message, and IP fields.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        $keyword = is_string($keyword) ? trim($keyword) : '';

        if ($keyword === '') {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($keyword): void {
            $subQuery->where('action', 'like', "%{$keyword}%")
                ->orWhere('level', 'like', "%{$keyword}%")
                ->orWhere('message', 'like', "%{$keyword}%")
                ->orWhere('ip_address', 'like', "%{$keyword}%");
        });
    }

    /**
     * Restrict logs to a given time range.
     */
    public function scopeBetweenDates(Builder $query, ?string $start, ?string $end): Builder
    {
        $startDate = static::parseBoundaryDate($start, true);
        $endDate = static::parseBoundaryDate($end, false);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Apply a collection of supported filters to the query.
     *
     * Supported filters: keyword, start, end, level, levels, action, user_id, ip_address.
     */
    public function scopeApplyFilters(Builder $query, array $filters = []): Builder
    {
        $query
            ->search($filters['keyword'] ?? null)
            ->betweenDates($filters['start'] ?? null, $filters['end'] ?? null);

        $levelFilter = $filters['levels'] ?? $filters['level'] ?? null;
        $levels = array_values(array_filter(array_map(
            static fn ($value): string => strtolower(trim((string) $value)),
            Arr::wrap($levelFilter)
        )));

        if ($levels !== []) {
            $query->whereIn('level', $levels);
        }

        if (isset($filters['action']) && trim((string) $filters['action']) !== '') {
            $query->where('action', trim((string) $filters['action']));
        }

        if (! empty($filters['user_id']) && is_numeric($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (isset($filters['ip_address']) && trim((string) $filters['ip_address']) !== '') {
            $query->where('ip_address', trim((string) $filters['ip_address']));
        }

        return $query;
    }

    protected static function parseBoundaryDate(?string $value, bool $isStart = true): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }

        return $isStart ? $date->copy()->startOfSecond() : $date->copy()->endOfSecond();
    }
}
