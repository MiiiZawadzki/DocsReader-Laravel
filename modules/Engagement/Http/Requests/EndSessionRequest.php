<?php

namespace Modules\Engagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Engagement\Http\Requests\Concerns\ResolvesReadingSession;
use Modules\Engagement\Http\Requests\Concerns\ValidatesTicks;

class EndSessionRequest extends FormRequest
{
    use ResolvesReadingSession;
    use ValidatesTicks;

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
        // The SPA's leave-the-page beacon carries the last buffered ticks along with the end
        return $this->tickRules('sometimes');
    }
}
