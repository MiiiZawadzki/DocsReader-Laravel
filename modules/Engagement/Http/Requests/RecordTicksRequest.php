<?php

namespace Modules\Engagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Engagement\Http\Requests\Concerns\ResolvesReadingSession;

class RecordTicksRequest extends FormRequest
{
    use ResolvesReadingSession;

    public function authorize(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $session = $this->readingSession();

        return $session !== null && $session->user_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'ticks' => ['required', 'array', 'min:1', 'max:200'],
            'ticks.*.clientEventId' => ['required', 'string', 'size:26'],
            'ticks.*.pageNumber' => ['required', 'integer', 'min:1'],
            'ticks.*.activeMs' => ['required', 'integer', 'min:1'],
            'ticks.*.occurredAt' => ['nullable', 'string'],
        ];
    }
}
