<?php

namespace Modules\ControlModule\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'external_id',
        'mac_address',
        'ip_address',
    ];

    public function nodes()
    {
        return $this->hasMany(Node::class);
    }

    public function nodeControllers()
    {
        return $this->nodes()->where('type', 'controller');
    }

    public function nodeSensors()
    {
        return $this->nodes()->where('type', 'sensor');
    }
}
