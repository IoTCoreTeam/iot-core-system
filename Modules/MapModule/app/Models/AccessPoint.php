<?php

namespace Modules\MapModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccessPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'ssid',
        'bssid',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fingerprintRssi()
    {
        return $this->hasMany(FingerprintRssi::class);
    }
}
