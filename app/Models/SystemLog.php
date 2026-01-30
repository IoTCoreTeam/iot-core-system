<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public static function countByWeekAndLevel($weeks = 5)
    {
        $startDate = \Carbon\Carbon::now()->subWeeks($weeks)->startOfWeek();

        $logs =  self::query()
            ->selectRaw('YEARWEEK(created_at, 1) as week, level, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('week', 'level')
            ->orderBy('week')
            ->get();

        // Prepare categories (weeks)
        $categories = [];
        $currentDate = $startDate->copy();
        for ($i = 0; $i <= $weeks; $i++) {
            $yearWeek = $currentDate->format('oW'); // ISO-8601 year and week number
            $categories[$yearWeek] = 'Week ' . $currentDate->week;
            $currentDate->addWeek();
        }

        // Initialize series data
        $seriesData = [
            'warning' => array_fill_keys(array_keys($categories), 0),
            'error'   => array_fill_keys(array_keys($categories), 0),
            'info'    => array_fill_keys(array_keys($categories), 0),
        ];

        // Fill series data
        foreach ($logs as $log) {
            $yearWeek = $log->week; // MySQL YEARWEEK returns YYYYWW
            if (isset($categories[$yearWeek])) {
                $level = strtolower($log->level);
                if (isset($seriesData[$level])) {
                    $seriesData[$level][$yearWeek] = $log->count;
                }
            }
        }

        return [
            'categories' => array_values($categories),
            'series' => [
                [
                    'name' => 'Error',
                    'data' => array_values($seriesData['error']),
                ],
                [
                    'name' => 'Warning',
                    'data' => array_values($seriesData['warning']),
                ],
                [
                    'name' => 'Info',
                    'data' => array_values($seriesData['info']),
                ],
            ],
        ];
    }
}
