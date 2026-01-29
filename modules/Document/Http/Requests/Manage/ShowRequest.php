<?php

namespace Modules\Document\Http\Requests\Manage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApiInterface;

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
        $documentApi = app()->make(DocumentApiInterface::class);

        return $documentApi->getManagerDocuments(Auth::id())->contains('uuid', $id);
    }
}
