<?php

namespace App\Enums;

enum UserRoles: int
{
    case SUPERUSER = 1;
    case USERS = 2;
}
