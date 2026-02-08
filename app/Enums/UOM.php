<?php

namespace App\Enums;

enum UOM: int
{
    case PIECE = 0;
    case TABLET = 1;
    case CAPSULE = 2;
    case ML = 3;
    case LITER = 4;
    case BOTTLE = 5;
    case VIAL = 6;
    case AMPULE = 7;
    case ROLL = 8;
    case PACK = 9;
    case BOX = 10;
    case PAIR = 11;
    case UNIT = 12;
    case SET = 13;

    public function label(): string
    {
        return str_replace('_', ' ', ucwords(strtolower($this->name)));
    }
}
