<?php

namespace Modules\History\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApiInterface;
use Modules\Engagement\Api\EngagementApiInterface;

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

        return app(DocumentApiInterface::class)
            ->verifyAssignedDocument(Auth::id(), $id);
    }

    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'session_uuid' => ['nullable', 'string', 'size:26'],
        ];
    }

    /**
     * @return void
     */
    protected function passedValidation(): void
    {
        $gateResult = app(EngagementApiInterface::class)
            ->evaluate(Auth::id(), (string)$this->route('document'));

        if (!$gateResult['allowed']) {
            throw new HttpResponseException(response()->json([
                'message' => __('engagement::messages.gate.incomplete'),
                'missing_pages' => $gateResult['missingPages'],
                'min_seconds_per_page' => $gateResult['minSecondsPerPage'],
                'total_pages' => $gateResult['totalPages'],
            ], 422));
        }
    }
}
