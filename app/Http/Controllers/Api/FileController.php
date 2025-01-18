<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Document\ShowRequest;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
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

        return Storage::disk('documents')->download($document->file_path);
    }
}
