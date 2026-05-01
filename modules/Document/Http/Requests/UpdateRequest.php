<?php

namespace Modules\Document\Http\Requests;

use App\Concerns\AuthorizesPermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Document\Concerns\ManagesOwnDocuments;

class UpdateRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'description' => 'string|nullable',
            'declaration' => 'string|nullable',
            'delay' => 'required|int',
            'date_from' => 'required|date',
            'date_to' => 'date|nullable',
            'file' => 'nullable|file|extensions:pdf',
        ];
    }
}
