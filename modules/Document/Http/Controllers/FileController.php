<?php

namespace Modules\Document\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Modules\Document\Http\Requests\GetFileRequest;
use Modules\Document\Services\DocumentService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController
{
    public function __construct(
        private readonly DocumentService $documentService,
    )
    {
    }

    /**
     * Return specified file from storage.
     *
     * @param GetFileRequest $request
     * @return StreamedResponse
     */
    public function get(GetFileRequest $request): StreamedResponse
    {
        $document = $this->documentService->getDocumentByUuid($request->route('document'));

        if (!Storage::disk('documents')->exists($document->file_path)) {
            abort(404, __('api.document.not_found'));
        }

        return Storage::disk('documents')->download($document->file_path, $document->source_name);
    }
}
