<?php

namespace App\Enums;

enum AppointmentStatus: int
{
    case PENDING = 0;
    case APPROVED = 1;
    case RESCHEDULED = 2;
    case CANCELLED = 3;
    case COMPLETED = 4;
    case NO_SHOW = 5;
}
