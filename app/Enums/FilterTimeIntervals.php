<?php

namespace App\Enums;

enum FilterTimeIntervals: int
{
    case DAILY = 0;
    case WEEKLY = 1;
    case MONTHLY = 2;
    case YEARLY = 3;
}
