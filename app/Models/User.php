<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use App\Models\Company;
use Carbon\Carbon;

class User extends Authenticatable implements OAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasApiTokens, HasFactory, Notifiable;

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected static function booted()
    {
        static::creating(function ($user) {
            $company = Company::first();
            if ($company) {
                $user->company_id = $company->id;
            }
        });
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orwhere('email', 'like', "%{$keyword}%")
              ->orwhere('role', 'like', "%{$keyword}%");
        });
    }

    public static function filterUsers(array $filters = [], int $perPage = 5)
    {
        return static::query()
            ->applyFilters($filters)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function scopeApplyFilters(Builder $query, array $filters = []): Builder
    {
        $keyword = trim($filters['keyword'] ?? '');
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('role', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        $role = trim($filters['role'] ?? '');
        if ($role !== '') {
            $query->where('role', $role);
        }

        $startDate = static::parseBoundaryDate($filters['start'] ?? null, true);
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        $endDate = static::parseBoundaryDate($filters['end'] ?? null, false);
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    protected static function parseBoundaryDate(?string $value, bool $isStart = true): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }

        return $isStart ? $date->copy()->startOfSecond() : $date->copy()->endOfSecond();
    }
}
