<?php

namespace App\Enums;

enum UserAccess: int
{
    case DASHBOARD = 0;


    case USERS = 1;

        //for students
    case STUDENT_DASHBOARD = 2;
    case APPOINTMENTS = 3;



    case OVERALL_APPOINTMENTS = 4;

    case DOCTOR_DASHBOARD = 5;
    case DOCTOR_APPOINTMENTS = 6;

    case ADD_VITAL_SIGN = 7;
    case TODAYS_APPOINTMENT = 8;

    case STAFF_DASHBOARD = 9;

    case ALL_PRODUCTS = 10;
    case MODIFY_PRODUCTS = 11;
}
