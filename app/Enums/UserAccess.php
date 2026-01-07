<?php

namespace App\Enums;

enum UserAccess: int
{
    case DASHBOARD = 0;


    case USERS = 1;

        //for students
    case STUDENT_DASHBOARD = 2;
    case APPOINTMENTS = 3;
}
