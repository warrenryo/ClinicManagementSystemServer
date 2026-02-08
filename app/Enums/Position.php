<?php

namespace App\Enums;

enum Position: int
{
    case POSITION_INSTRUCTOR = 0;
    case POSITION_ASSISTANT_PROFESSOR = 1;
    case POSITION_ASSOCIATE_PROFESSOR = 2;
    case POSITION_PROFESSOR = 3;
    case POSITION_GUIDANCE_COUNSELOR = 4;
    case POSITION_DEPARTMENT_HEAD = 5;

    public function label(): string
    {
        return match ($this) {
            // Positions
            self::POSITION_INSTRUCTOR => 'Instructor',
            self::POSITION_ASSISTANT_PROFESSOR => 'Assistant Professor',
            self::POSITION_ASSOCIATE_PROFESSOR => 'Associate Professor',
            self::POSITION_PROFESSOR => 'Professor',
            self::POSITION_GUIDANCE_COUNSELOR => 'Guidance Counselor',
            self::POSITION_DEPARTMENT_HEAD => 'Department Head',
        };
    }
}
