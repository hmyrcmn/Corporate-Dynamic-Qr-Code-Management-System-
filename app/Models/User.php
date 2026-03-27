<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\HasLdapUser;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class User extends Authenticatable implements FilamentUser, LdapAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use AuthenticatesWithLdap;
    use HasFactory;
    use HasLdapUser;
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'department_id',
        'role',
        'is_active',
        'last_login_at',
    ];

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
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if ($user->role !== UserRole::SUPER_ADMIN->value) {
                return;
            }

            $conflictExists = static::query()
                ->where('role', UserRole::SUPER_ADMIN->value)
                ->whereKeyNot($user->getKey())
                ->exists();

            if ($conflictExists) {
                throw ValidationException::withMessages([
                    'role' => 'Sistemde yalnizca bir adet Super Admin bulunabilir.',
                ]);
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function createdQrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class, 'created_by_id');
    }

    public function hasGlobalAccess(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN->value;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasGlobalAccess();
    }
}
