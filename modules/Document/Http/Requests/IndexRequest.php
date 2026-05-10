<?php

namespace Modules\Document\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Modules\Document\DTO\DocumentListFiltersDTO;
use Modules\Document\Enums\DocumentReadStatus;

class IndexRequest extends FormRequest
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
            'status' => ['nullable', new Enum(DocumentReadStatus::class)],
            'q' => ['nullable', 'string'],
        ];
    }

    public function toFilters(): DocumentListFiltersDTO
    {
        $status = $this->input('status');

        return new DocumentListFiltersDTO(
            status: $status !== null ? DocumentReadStatus::from($status) : null,
            query: $this->input('q'),
        );
    }
}
