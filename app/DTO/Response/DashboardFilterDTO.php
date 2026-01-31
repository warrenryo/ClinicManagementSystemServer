<?php

namespace App\DTO\Response;

use App\Enums\FilterTimeIntervals;

class DashboardFilterDTO
{
    public function __construct(
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $dateTime = null,
        public ?FilterTimeIntervals $filterTimeIntervals = null
    ) {}
}
