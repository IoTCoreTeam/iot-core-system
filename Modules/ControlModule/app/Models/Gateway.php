<?php

namespace Modules\ControlModule\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gateway extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'external_id',
        'mac_address',
        'ip_address',
        'registration_status',
    ];

    protected $casts = [
        'registration_status' => 'boolean',
    ];

    public function nodes()
    {
        return $this->hasMany(Node::class);
    }

    public function nodeControllers()
    {
        return $this->hasManyThrough(NodeController::class, Node::class, 'gateway_id', 'node_id');
    }

    public function nodeSensors()
    {
        return $this->hasManyThrough(NodeSensor::class, Node::class, 'gateway_id', 'node_id');
    }
}
