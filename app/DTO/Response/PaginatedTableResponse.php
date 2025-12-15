<?php

namespace App\DTO\Response;

class PaginatedTableResponse
{
    public array $responseData;
    public int $count;

    public function __construct(array $responseData, int $count)
    {
        $this->responseData = $responseData;
        $this->count = $count;
    }

    public function toArray(): array
    {
        return [
            'ResponseData' => $this->responseData,
            'Count' => $this->count,
        ];
    }
}
