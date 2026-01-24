<?php

namespace Modules\ControlModule\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gateway extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected static function newFactory()
    {
        return \Modules\ControlModule\Database\Factories\GatewayFactory::new();
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'external_id',
        'connection_key',
        'location',
        'ip_address',
        'description',
        'registration_status',
    ];

    protected $casts = [
        'registration_status' => 'boolean',
    ];

    public function scopeSearch($query, $keyword)
    {
        $boolKeyword = filter_var($keyword, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $query->where('name', 'like', "%{$keyword}%")
            ->orWhere('external_id', 'like', "%{$keyword}%")
            ->orWhere('location', 'like', "%{$keyword}%")
            ->orWhere('ip_address', 'like', "%{$keyword}%")
            ->when($boolKeyword !== null, fn($query) => $query->orWhere('registration_status', $boolKeyword));
    }

    public function nodes()
    {
        return $this->hasMany(Node::class);
    }

    public function nodeControllers()
    {
        return $this->hasMany(NodeController::class);
    }

    public function nodeSensors()
    {
        return $this->hasMany(NodeSensor::class);
    }
}
