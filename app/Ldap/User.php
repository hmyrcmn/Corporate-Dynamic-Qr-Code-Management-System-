<?php

namespace App\Ldap;

use LdapRecord\Models\ActiveDirectory\User as ActiveDirectoryUser;
use LdapRecord\Query\Model\Builder;

class User extends ActiveDirectoryUser
{
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('only_enabled_accounts', function (Builder $query): void {
            if (config('dynamicqr.ldap_only_enabled_users')) {
                $query->whereEnabled();
            }
        });

        static::addGlobalScope('custom_user_filter', function (Builder $query): void {
            $filter = trim((string) config('dynamicqr.ldap_user_filter'));

            if ($filter !== '') {
                $query->rawFilter($filter);
            }
        });
    }
}
