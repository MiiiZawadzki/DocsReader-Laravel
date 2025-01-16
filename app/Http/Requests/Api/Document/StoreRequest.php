<?php

namespace App\Http\Requests\Api\Document;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO:- check for permission
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
            'title' => 'required|string',
            'description' => 'string|nullable',
            'declaration' => 'string|nullable',
            'delay' => 'required|int',
            'date_from' => 'required|date',
            'date_to' => 'date|nullable',
            'file' => 'required|file|extensions:pdf',
        ];
    }
}
