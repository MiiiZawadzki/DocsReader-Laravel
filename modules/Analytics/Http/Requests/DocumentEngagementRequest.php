<?php

namespace Modules\Analytics\Http\Requests;

use App\Concerns\AuthorizesPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApiInterface;

class DocumentEngagementRequest extends FormRequest
{
    use AuthorizesPermissions;

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        $documentUuid = $this->route('document');
        if (! is_string($documentUuid) || $documentUuid === '') {
            return false;
        }

        return $this->userHasPermission('manage-documents')
            && app(DocumentApiInterface::class)->isManagerOf(Auth::id(), $documentUuid);
    }
}
