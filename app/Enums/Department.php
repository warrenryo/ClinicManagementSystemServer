<?php

namespace App\Enums;

enum Department: int
{
    case DEPT_IT = 0;
    case DEPT_BUSINESS = 1;
    case DEPT_EDUCATION = 2;
    case DEPT_HOSPITALITY = 3;
    case DEPT_OFFICE_ADMIN = 4;
    case DEPT_CRIMINOLOGY = 5;
    case DEPT_PSYCHOLOGY = 6;
    case DEPT_LIBRARY_SCIENCE = 7;

    public function label(): string
    {
        return match ($this) {
            // Departments
            self::DEPT_IT => 'Information Technology',
            self::DEPT_BUSINESS => 'Business Administration',
            self::DEPT_EDUCATION => 'Education',
            self::DEPT_HOSPITALITY => 'Hospitality Management',
            self::DEPT_OFFICE_ADMIN => 'Office Administration',
            self::DEPT_CRIMINOLOGY => 'Criminology',
            self::DEPT_PSYCHOLOGY => 'Psychology',
            self::DEPT_LIBRARY_SCIENCE => 'Library & Info Science',
        };
    }
}
