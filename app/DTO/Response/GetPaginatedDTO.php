<?php

namespace App\DTO\Response;

class GetPaginatedDTO
{
    public int $take = 10;
    public int $skip = 0;
    public ?string $searchValue = null;


    public function __construct(int $take, int $skip, ?string $searchValue)
    {
        $this->take = $take;
        $this->skip = $skip;
        $this->searchValue = $searchValue;
    }
}
