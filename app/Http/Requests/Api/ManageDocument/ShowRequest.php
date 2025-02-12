<?php

namespace App\Http\Requests\Api\ManageDocument;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ShowRequest extends FormRequest
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
            ->forManager(Auth::user())
            ->exists();
    }
}
