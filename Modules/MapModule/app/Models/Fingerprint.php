<?php

namespace Modules\MapModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fingerprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'map_id',
        'x',
        'y',
    ];

    protected $casts = [
        'x' => 'float',
        'y' => 'float',
    ];

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function rssi()
    {
        return $this->hasMany(FingerprintRssi::class);
    }
}
