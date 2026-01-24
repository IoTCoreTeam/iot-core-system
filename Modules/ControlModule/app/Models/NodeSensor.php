<?php

namespace Modules\ControlModule\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NodeSensor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected static function newFactory()
    {
        return \Modules\ControlModule\Database\Factories\NodeSensorFactory::new();
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'node_id',
        'external_id',
        'name',
        'sensor_type',
        'last_reading',
        'limit_value',
        'registration_status',
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
            $sensorQuery->where('name', 'like', "%{$keyword}%")
                ->orWhere('external_id', 'like', "%{$keyword}%")
                ->orWhere('sensor_type', 'like', "%{$keyword}%")
                ->orWhere('registration_status', 'like', "%{$keyword}%");
        });
    }
}
