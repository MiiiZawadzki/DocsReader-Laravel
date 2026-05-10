<?php

namespace Modules\History\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\History\DTO\HistoryListFiltersDTO;

class GetHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string'],
        ];
    }

    public function toFilters(): HistoryListFiltersDTO
    {
        return new HistoryListFiltersDTO(
            query: $this->input('q'),
        );
    }
}
