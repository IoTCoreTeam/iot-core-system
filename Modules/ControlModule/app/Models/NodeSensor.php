<?php

namespace Modules\ControlModule\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodeSensor extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'node_id',
        'sensor_type',
        'last_reading',
        'limit_value',
    ];

    protected $casts = [
        'last_reading' => 'decimal:4',
        'limit_value' => 'decimal:4',
    ];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if (! $keyword) {
            return $query;
        }

        $keyword = trim($keyword);

        return $query->where(function ($sensorQuery) use ($keyword) {
            $sensorQuery->where('sensor_type', 'like', "%{$keyword}%")
                ->orWhereHas('node', function ($nodeQuery) use ($keyword) {
                    $nodeQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('external_id', 'like', "%{$keyword}%");
                });
        });
    }
}
