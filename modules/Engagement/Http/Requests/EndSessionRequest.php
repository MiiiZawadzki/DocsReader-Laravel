<?php

namespace Modules\Engagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Engagement\Http\Requests\Concerns\ResolvesReadingSession;

class EndSessionRequest extends FormRequest
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
}
