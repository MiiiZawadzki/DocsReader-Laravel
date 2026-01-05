<?php

namespace Modules\Document\DTO;

use Illuminate\Http\UploadedFile;
use Modules\Document\Http\Requests\UpdateRequest;
use Modules\Document\Models\Document;

readonly class UpdateDocumentDTO
{

    public function __construct(private string $uuid, private UpdateRequest $request)
    {
    }

    public function getFile(): ?UploadedFile
    {
        return $this->request->file('file');
    }

    public function getModel(): Document
    {
        return Document::where('uuid', $this->uuid)->first();
    }

    public function getFormData(): array
    {
        $baseData = $this->request->only([
            'title',
            'description',
            'declaration',
            'delay',
            'date_from',
            'date_to',
            'file',
        ]);

        $baseData['uuid'] = $this->uuid;

        return $baseData;
    }
}
