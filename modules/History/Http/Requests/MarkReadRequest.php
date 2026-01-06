<?php

namespace Modules\History\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApi;

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

        $documentApi = app()->make(DocumentApi::class);

        return $documentApi->verifyAssignedDocument(Auth::id(), $id);
    }
}
