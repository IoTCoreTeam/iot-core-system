<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('action', 'like', "%{$keyword}%")
              ->orWhere('level', 'like', "%{$keyword}%")
              ->orWhere('message', 'like', "%{$keyword}%")
              ->orWhere('ip_address', 'like', "%{$keyword}%")
              ->orWhere('context', 'like', "%{$keyword}%");
        });
    }

    public function scopeKeyword(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($sub) use ($keyword) {
            $sub->where('message', 'like', "%{$keyword}%")
                ->orWhere('action', 'like', "%{$keyword}%")
                ->orWhere('context', 'like', "%{$keyword}%");
        });
    }

    public function scopeTime(Builder $query, ?string $start, ?string $end): Builder
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
