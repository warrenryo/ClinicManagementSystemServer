<?php

namespace App\Enums;

use App\Helpers\SearchableEnums;

enum DoctorSpecialization: int
{
    use SearchableEnums;
    case GENERAL_PRACTITIONER = 0;
    case PEDIATRICIAN = 1;
    case CARDIOLOGIST = 2;
    case DERMATOLOGIST = 3;
    case ORTHOPEDIC = 4;
    case NEUROLOGIST = 5;
    case PSYCHIATRIST = 6;
    case GYNECOLOGIST = 7;
    case OPHTHALMOLOGIST = 8;
    case ENT = 9;
    case RADIOLOGIST = 10;
    case ANESTHESIOLOGIST = 11;
    case UROLOGIST = 12;
    case ONCOLOGIST = 13;
}
