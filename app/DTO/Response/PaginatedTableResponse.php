<?php

namespace App\DTO\Response;

class PaginatedTableResponse
{
    public array $ResponseData;
    public int $Count;

    public function __construct(array $responseData, int $count)
    {
        $this->ResponseData = $responseData;
        $this->Count = $count;
    }

    public function toArray(): array
    {
        return [
            'ResponseData' => $this->ResponseData,
            'Count' => $this->Count,
        ];
    }
}
