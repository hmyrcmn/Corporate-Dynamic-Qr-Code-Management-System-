<?php

namespace App\Enums;

enum UserRole: string
{
    case DEPT_MANAGER = 'DEPT_MANAGER';
    case DEPT_USER = 'DEPT_USER';
}
