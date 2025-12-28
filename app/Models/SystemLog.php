<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SystemLog extends Model
{
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


    public static function normalizePerPage(?int $perPage, int $default = 15): int
    {
        return max(1, min($perPage ?? $default, 100));
    }

    protected static function baseQuery()
    {
        return static::with('user:id,name,email')
            ->orderByDesc('created_at');
    }

    public static function findWithUser(int|string $id): self
    {
        return static::baseQuery()->findOrFail($id);
    }

    public static function searchByKeywords(array $keywords, int $perPage = 15)
    {
        $terms = static::normalizeSearchTerms($keywords);

        $query = static::baseQuery();

        if ($terms === []) {
            return $query->paginate($perPage);
        }

        return $query->where(function ($outer) use ($terms) {
            foreach ($terms as $term) {
                $outer->orWhere(function ($inner) use ($term) {
                    $likeValue = "%{$term}%";

                    $inner->where('action', 'like', $likeValue)
                        ->orWhere('level', 'like', $likeValue)
                        ->orWhere('message', 'like', $likeValue)
                        ->orWhere('ip_address', 'like', $likeValue);
                });
            }
        })->paginate($perPage);
    }

    public static function filterLogs(array $filters, int $perPage = 15)
    {
        return static::applyFilters(static::baseQuery(), $filters)
            ->paginate($perPage);
    }

    public static function availableLevels(): array
    {
        return static::query()
            ->select('level')
            ->distinct()
            ->pluck('level')
            ->filter(static fn ($level) => is_string($level) && trim($level) !== '')
            ->map(static fn ($level) => strtolower(trim((string) $level)))
            ->unique()
            ->values()
            ->all();
    }

    public static function normalizeLevels(null|string|array $levels): array
    {
        $values = is_array($levels) ? $levels : [$levels];

        return array_values(array_filter(array_map(
            static fn ($value) => strtolower(trim((string) $value)),
            array_filter($values, static fn ($value) => $value !== null)
        ), static fn ($value) => $value !== ''));
    }

    public static function normalizeFilters(array $input): array
    {
        $action = isset($input['action']) ? trim((string) $input['action']) : '';
        $levels = static::normalizeLevels($input['levels'] ?? null);
        $userIdInput = $input['user_id'] ?? null;
        $userId = is_numeric($userIdInput) ? (int) $userIdInput : null;
        $ipValue = $input['ip_address'] ?? '';
        $ipAddress = is_string($ipValue) ? trim($ipValue) : '';
        $start = static::parseBoundaryDate($input['start'] ?? null, true);
        $end = static::parseBoundaryDate($input['end'] ?? null, false);

        return [
            'action' => $action,
            'levels' => $levels,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'start' => $start,
            'end' => $end,
        ];
    }

    protected static function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['levels'])) {
            $query->whereIn('level', (array) $filters['levels']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        if (! empty($filters['start'])) {
            $query->where('created_at', '>=', $filters['start']);
        }

        if (! empty($filters['end'])) {
            $query->where('created_at', '<=', $filters['end']);
        }

        return $query;
    }

    protected static function normalizeSearchTerms(array $values): array
    {
        $terms = [];

        foreach ($values as $value) {
            if (! is_string($value)) {
                continue;
            }

            $trimmed = trim($value);

            if ($trimmed === '' || in_array($trimmed, $terms, true)) {
                continue;
            }

            $terms[] = $trimmed;
        }

        return $terms;
    }

    protected static function parseBoundaryDate($value, bool $isStart = true): ?Carbon
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
