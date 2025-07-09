<?php 

namespace App\Enums;

enum UserRole: string
{
    case TENANT = 'tenant';
    case ADMIN = 'admin';
    case USER = 'manager';
}