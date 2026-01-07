<?php

namespace App\Enums;

enum UserRoles: int
{
    case SUPERUSER = 0;
    case STUDENTS = 1;
    case USERS = 2;
    case DOCTORS = 3;
    case NURSES = 4;
    case CLINIC_STAFF = 5;
}
