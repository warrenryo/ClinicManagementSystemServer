<?php

namespace App\DTO\Response;


class GetPaginatedDTO
{
    public int $Take = 10;
    public int $Skip = 0;
    public ?string $SearchValue = null;
    public ?string $Date = null;

    public function __construct(
        int $Take,
        int $Skip,
        ?string $SearchValue,
        ?string $Date
    ) {
        $this->Take = $Take;
        $this->Skip = $Skip;
        $this->SearchValue = $SearchValue;
        $this->Date = $Date;
    }
}
