<?php

namespace App\Enums;

enum AppointmentReasons: int
{
    case FEVER_OR_FLU_LIKE_SYMPTOMS = 0;
    case HEADACHE_OR_MIGRAINE = 1;
    case STOMACHACHE_OR_DIGESTIVE_PROBLEMS = 2;

    case MINOR_INJURY_OR_ACCIDENT = 3;
    case ALLERGY_OR_ASTHMA_RELATED_SYMPTOMS = 4;
    case DENTAL_PAIN_OR_ORAL_HEALTH_CONCERNS = 5;
    case SKIN_CONDITIONS_OR_RASHES = 6;

    case MEDICAL_CLEARANCE_OR_HEALTH_CERTIFICATION = 7;
    case FOLLOW_UP_CHECK_UP = 8;
    case OTHER_HEALTH_CONCERNS = 9;

    public function label(): string
    {
        return str_replace('_', ' ', ucwords(strtolower($this->name)));
    }
}
