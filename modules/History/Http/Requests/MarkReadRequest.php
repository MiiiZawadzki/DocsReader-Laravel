<?php

namespace Modules\History\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkReadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO:- implement this logic
        return true;
//        if (!Auth::check() || !$id = $this->route('document')) {
//            return false;
//        }
//
//        return Document::where('uuid', $id)
//            ->forUser(Auth::user())
//            ->exists();
    }
}
