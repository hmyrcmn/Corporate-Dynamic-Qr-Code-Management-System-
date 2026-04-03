<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_id',
        'department_id',
        'created_by_id',
        'title',
        'destination_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $qrCode): void {
            if ($qrCode->short_id) {
                return;
            }

            do {
                $shortId = Str::lower(Str::random(6));
            } while (static::query()->where('short_id', $shortId)->exists());

            $qrCode->short_id = $shortId;
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(ScanAnalytics::class);
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasGlobalDepartmentAccess()) {
            return $query;
        }

        if ($user->department_id) {
            return $query->where('department_id', $user->department_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
