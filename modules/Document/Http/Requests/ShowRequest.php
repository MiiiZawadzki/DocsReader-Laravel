<?php

namespace Modules\Document\Http\Requests;

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
        return true;
//        if (!Auth::check() || !$id = $this->route('document')) {
//            return false;
//        }
//
//        $isAssigned = Document::where('uuid', $id)
//            ->forUser(Auth::user())
//            ->exists();
//
//        $isManager = Document::where('uuid', $id)
//            ->forManager(Auth::user())
//            ->exists();
//
//        return $isAssigned || $isManager;
    }
}
