<?php

namespace App\Http\Requests\Requests;

use App\DTO\Response\GetPaginatedDTO;
use App\Helpers\Token;
use Illuminate\Foundation\Http\FormRequest;

class GetPaginatedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Take' => 'integer|min:1|max:500',
            'Skip' => 'integer',
            'SearchValue' => 'nullable|string|max:255',
            'ApprovalStatus' => 'integer'
        ];
    }

    public function toDTO(): GetPaginatedDTO
    {
        return new GetPaginatedDTO(
            Take: $this->input('Take', 10),
            Skip: $this->input('Skip', 0),
            SearchValue: $this->input('SearchValue'),
            Date: $this->input('Date'),
            ApprovalStatus: $this->input('ApprovalStatus')
        );
    }
}
