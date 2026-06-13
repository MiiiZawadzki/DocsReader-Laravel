<?php

namespace Modules\Engagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApiInterface;

class StartSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $uuid = $this->input('documentUuid');
        if (! is_string($uuid) || $uuid === '') {
            return false;
        }

        return app(DocumentApiInterface::class)->verifyAssignedDocument(Auth::id(), $uuid);
    }

    public function rules(): array
    {
        return [
            'documentUuid' => ['required', 'string'],
            'clientMeta' => ['nullable', 'array'],
        ];
    }
}
