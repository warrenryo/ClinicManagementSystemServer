<?php

namespace App\Enums;

enum Course: int
{
    case BACHELOR_OF_SECONDARY_EDUCATION = 0;
    case BACHELOR_OF_ELEMENTARY_EDUCATION = 1;

        // IT & ENGINEERING
    case BACHELOR_OF_SCIENCE_IN_INFORMATION_TECHNOLOGY = 2;
    case BACHELOR_OF_SCIENCE_IN_COMPUTER_ENGINEERING = 3;

        // BUSINESS & MANAGEMENT
    case BACHELOR_OF_SCIENCE_IN_BUSINESS_ADMINISTRATION = 4;
    case BACHELOR_OF_SCIENCE_IN_OFFICE_ADMINISTRATION = 5;

        // HOSPITALITY
    case BACHELOR_OF_SCIENCE_IN_HOTEL_AND_RESTAURANT_MANAGEMENT = 6;

        // CRIMINOLOGY
    case BACHELOR_OF_SCIENCE_IN_CRIMINOLOGY = 7;

        // LIBRARY SCIENCE
    case BACHELOR_OF_LIBRARY_AND_INFORMATION_SCIENCE = 8;


    public function label(): string
    {
        return str_replace('_', ' ', ucwords(strtolower($this->name)));
    }
}
