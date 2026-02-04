<?php

namespace Modules\MapModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FingerprintRssi extends Model
{
    use HasFactory;

    protected $table = 'fingerprint_rssi';

    public $incrementing = false;
    protected $primaryKey = ['fingerprint_id', 'access_point_id'];

    protected $fillable = [
        'fingerprint_id',
        'access_point_id',
        'rssi',
    ];

    protected $casts = [
        'rssi' => 'integer',
    ];

    public function fingerprint()
    {
        return $this->belongsTo(Fingerprint::class);
    }

    public function accessPoint()
    {
        return $this->belongsTo(AccessPoint::class);
    }
}
