<?php

namespace App\Http\Requests\Api\Document;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MarkReadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!Auth::check() || !$id = $this->route('document')) {
            return false;
        }

        return Document::where('uuid', $id)
            ->forUser(Auth::user())
            ->exists();
    }
}
