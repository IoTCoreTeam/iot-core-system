<?php

namespace Modules\ControlModule\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NodeController extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected static function newFactory()
    {
        return \Modules\ControlModule\Database\Factories\NodeControllerFactory::new();
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'node_id',
        'external_id',
        'name',
        'firmware_version',
        'registration_status',
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

        return $query->where(function ($controllerQuery) use ($keyword) {
            $controllerQuery->where('name', 'like', "%{$keyword}%")
                ->orWhere('external_id', 'like', "%{$keyword}%")
                ->orWhere('firmware_version', 'like', "%{$keyword}%")
                ->orWhere('registration_status', 'like', "%{$keyword}%");
        });
    }
}
