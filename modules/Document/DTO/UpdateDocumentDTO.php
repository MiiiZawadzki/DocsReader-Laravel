<?php

namespace Modules\Document\DTO;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Http\Requests\UpdateRequest;
use Modules\Document\Models\Document;

class UpdateDocumentDTO
{
//    public User $user;

    public function __construct(private readonly string $uuid, private readonly UpdateRequest $request)
    {
//        $this->user = Auth::user();
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
