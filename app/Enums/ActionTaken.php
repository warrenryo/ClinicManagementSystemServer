<?php

namespace App\Enums;

enum ActionTaken: int
{
    case RESTED = 0;
    case MEDICATION_GIVEN = 1;
    case SENT_HOME = 2;
    case FIRST_AID = 3;
    case REFERRED = 4;

    public function label(): string
    {
        return str_replace('_', ' ', ucwords(strtolower($this->name)));
    }
}
