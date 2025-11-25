<?php

namespace Modules\Document\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Modules\Document\Http\Requests\ShowRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController
{
    /**
     * Return specified file from storage.
     *
     * @param ShowRequest $request
     * @return StreamedResponse
     */
    public function get(ShowRequest $request): StreamedResponse
    {
        $document = Document::where('uuid', $request->route('document'))->first();

        if (!Storage::disk('documents')->exists($document->file_path)) {
            abort(404, __('api.document.not_found'));
        }

        return Storage::disk('documents')->download($document->file_path, $document->source_name);
    }
}
