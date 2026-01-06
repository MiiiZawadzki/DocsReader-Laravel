<?php

namespace Modules\Document\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApi;

class GetFileRequest extends FormRequest
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

        $isAssigned = $documentApi->verifyAssignedDocument(Auth::id(), $id);
        $isManager = $documentApi->getManagerDocuments(Auth::id())->contains('uuid', $id);

        return $isAssigned || $isManager;
    }
}
