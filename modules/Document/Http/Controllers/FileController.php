<?php

namespace Modules\Document\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Modules\Document\Http\Requests\ShowRequest;
use Modules\Document\Services\DocumentService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController
{
    public function __construct(
        private DocumentService $documentService,
    )
    {
    }

    /**
     * Return specified file from storage.
     *
     * @param ShowRequest $request
     * @return StreamedResponse
     */
    public function get(ShowRequest $request): StreamedResponse
    {
        $document = $this->documentService->getDocumentByUuid($request->route('document'));

        if (!Storage::disk('documents')->exists($document->file_path)) {
            abort(404, __('api.document.not_found'));
        }

        return Storage::disk('documents')->download($document->file_path, $document->source_name);
    }
}
