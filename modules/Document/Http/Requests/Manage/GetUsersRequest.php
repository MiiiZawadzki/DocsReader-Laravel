<?php

namespace Modules\Document\Http\Requests\Manage;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetUsersRequest extends FormRequest
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
}
