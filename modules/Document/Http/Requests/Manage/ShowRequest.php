<?php

namespace Modules\Document\Http\Requests\Manage;

use App\Concerns\AuthorizesPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Document\Concerns\ManagesOwnDocuments;

class ShowRequest extends FormRequest
{
    use AuthorizesPermissions;
    use ManagesOwnDocuments;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->userHasPermission('manage-documents')
            && $this->userManagesRouteDocument();
    }
}
