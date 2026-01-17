<?php

namespace App\Enums;

use App\Helpers\SearchableEnums;

enum UserRoles: int
{
    use SearchableEnums;
    case SUPERUSER = 0;
    case STUDENTS = 1;
    case DOCTORS = 2;
    case STAFF = 3;
    case TEACHERS = 4;
}
