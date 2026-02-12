<?php

namespace Modules\ControlModule\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodeController extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'node_id',
        'firmware_version',
        'control_url',
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
            $controllerQuery->where('firmware_version', 'like', "%{$keyword}%")
                ->orWhere('control_url', 'like', "%{$keyword}%")
                ->orWhereHas('node', function ($nodeQuery) use ($keyword) {
                    $nodeQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('external_id', 'like', "%{$keyword}%");
                });
        });
    }
}
