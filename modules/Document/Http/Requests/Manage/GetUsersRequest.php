<?php

namespace Modules\Document\Http\Requests\Manage;

use App\Concerns\AuthorizesPermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Document\Concerns\ManagesOwnDocuments;

class GetUsersRequest extends FormRequest
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

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'string', 'in:assigned,unassigned'],
        ];
    }
}
