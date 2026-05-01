<?php

namespace Modules\Document\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApiInterface;
use Modules\Document\Concerns\ManagesOwnDocuments;

class GetFileRequest extends FormRequest
{
    use ManagesOwnDocuments;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! Auth::check() || ! $id = $this->route('document')) {
            return false;
        }

        if ($this->userManagesRouteDocument()) {
            return true;
        }

        return app()->make(DocumentApiInterface::class)
            ->verifyAssignedDocument(Auth::id(), $id);
    }
}
