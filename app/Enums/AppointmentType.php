<?php

namespace App\Enums;

enum AppointmentType: int
{
    case SCHEDULED = 0;
    case WALK_IN = 1;
    case RESCHEDULED = 2;
}
