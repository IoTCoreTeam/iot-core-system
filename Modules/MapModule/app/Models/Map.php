<?php

namespace Modules\MapModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Map extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'name',
        'image_url',
        'width_px',
        'height_px',
        'scale_m_per_px',
        'description',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function fingerprints()
    {
        return $this->hasMany(Fingerprint::class);
    }

    protected static function newFactory()
    {
        return \Modules\MapModule\Database\Factories\MapFactory::new();
    }
}
