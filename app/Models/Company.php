<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    public function users()
    {
        return $this->hasMany(User::class);
    }
    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'fax',
    ];

    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;
}
