<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\HasLdapUser;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class User extends Authenticatable implements LdapAuthenticatable
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function createdQrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class, 'created_by_id');
    }

    public function hasGlobalDepartmentAccess(): bool
    {
        $globalAccessEmail = trim(mb_strtolower((string) config('dynamicqr.global_access_email')));

        if ($globalAccessEmail === '') {
            return false;
        }

        return in_array($globalAccessEmail, $this->globalAccessIdentifiers(), true);
    }

    public function departmentContextLabel(): string
    {
        if ($this->hasGlobalDepartmentAccess()) {
            return 'Tum Birimler';
        }

        return $this->department?->name ?? 'Atanmamis Birim';
    }

    /**
     * @return array<int, string>
     */
    private function globalAccessIdentifiers(): array
    {
        $identifiers = [];

        if (filled($this->email)) {
            $identifiers[] = mb_strtolower(trim((string) $this->email));
        }

        if (filled($this->username)) {
            $normalizedUsername = mb_strtolower(trim((string) $this->username));
            $identifiers[] = $normalizedUsername;

            $domainCandidates = array_filter([
                trim((string) $this->domain),
                trim((string) config('dynamicqr.ldap_domain')),
            ]);

            foreach ($domainCandidates as $domain) {
                $identifiers[] = $normalizedUsername.'@'.mb_strtolower($domain);
            }
        }

        return array_values(array_unique(array_filter($identifiers)));
    }
}
