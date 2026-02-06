<?php

namespace Modules\ControlModule\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Node extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected static function newFactory()
    {
        return \Modules\ControlModule\Database\Factories\NodeFactory::new();
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'gateway_id',
        'external_id',
        'name',
        'mac_address',
        'ip_address',
        'registration_status',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }

    public function controllers()
    {
        return $this->hasMany(NodeController::class);
    }

    public function sensors()
    {
        return $this->hasMany(NodeSensor::class);
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if (! $keyword) {
            return $query;
        }

        $keyword = trim($keyword);

        return $query->where(function ($nodeQuery) use ($keyword) {
            $nodeQuery->where('name', 'like', "%{$keyword}%")
                ->orWhere('external_id', 'like', "%{$keyword}%")
                ->orWhere('registration_status', 'like', "%{$keyword}%");
        });
    }
}
