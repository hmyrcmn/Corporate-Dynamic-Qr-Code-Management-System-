<?php

return [
    'ldap_enabled' => filter_var(env('LDAP_ENABLED', false), FILTER_VALIDATE_BOOL),
    'ldap_login_attribute' => (string) env('LDAP_LOGIN_ATTRIBUTE', 'samaccountname'),
    'ldap_display_attribute' => (string) env('LDAP_DISPLAY_ATTRIBUTE', 'displayname'),
    'ldap_email_attribute' => (string) env('LDAP_EMAIL_ATTRIBUTE', 'mail'),
    'ldap_department_attribute' => (string) env('LDAP_DEPARTMENT_ATTRIBUTE', 'department'),
    'ldap_domain' => trim((string) env('LDAP_DOMAIN', '')),
    'ldap_netbios_domain' => trim((string) env('LDAP_NETBIOS_DOMAIN', '')),
    'ldap_user_filter' => trim((string) env('LDAP_USER_FILTER', '')),
    'ldap_only_enabled_users' => filter_var(env('LDAP_ONLY_ENABLED_USERS', true), FILTER_VALIDATE_BOOL),
    'ldap_force_user_bind' => filter_var(env('LDAP_FORCE_USER_BIND', false), FILTER_VALIDATE_BOOL),
    'allowed_qr_domains' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ALLOWED_QR_DOMAINS', 'yee.org.tr,gov.tr,youtube.com'))
    ))),
    'ip_hash_salt' => env('IP_HASH_SALT', 'dynamicqr-laravel-dev-salt'),
    'super_admin_username' => (string) env('LDAP_SUPER_ADMIN_USERNAME', ''),
    'local_super_admin_enabled' => filter_var(env('LOCAL_SUPER_ADMIN_ENABLED', true), FILTER_VALIDATE_BOOL),
    'local_super_admin_username' => (string) env('LOCAL_SUPER_ADMIN_USERNAME', 'admin'),
    'local_super_admin_password' => (string) env('LOCAL_SUPER_ADMIN_PASSWORD', 'ChangeMe123!'),
];
