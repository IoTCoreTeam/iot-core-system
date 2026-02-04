<?php

namespace Modules\MapModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'height_m',
    ];

    public function maps()
    {
        return $this->hasMany(Map::class);
    }
}
