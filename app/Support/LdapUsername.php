<?php

namespace App\Support;

final class LdapUsername
{
    public static function normalize(string $username): string
    {
        $normalized = trim($username);

        if ($normalized === '') {
            return '';
        }

        if (str_contains($normalized, '\\')) {
            $segments = preg_split('/\\\\+/', $normalized);
            $normalized = end($segments) ?: $normalized;
        }

        if (str_contains($normalized, '@')) {
            $normalized = explode('@', $normalized, 2)[0];
        }

        return mb_strtolower(trim($normalized));
    }
}
