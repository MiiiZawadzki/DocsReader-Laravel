<?php

namespace Modules\Document\Http\Requests\Manage;

use App\Models\Document;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AssignUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        // TODO:- check for permission
//        if (!Auth::check() || !$id = $this->route('document')) {
//            return false;
//        }
//
//        return Document::where('uuid', $id)
//            ->forManager(Auth::user())
//            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assign' => 'required|boolean',
        ];
    }
}
