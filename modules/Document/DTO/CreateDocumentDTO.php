<?php

namespace Modules\Document\DTO;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Http\Requests\StoreRequest;

class CreateDocumentDTO
{
    private int $userId;

    public function __construct(private readonly StoreRequest $request)
    {
        $this->userId = Auth::id();
    }

    public function getFile(): UploadedFile
    {
        return $this->request->file('file');
    }

    public function getFormData(): array
    {
        return $this->request->only([
            'title',
            'description',
            'declaration',
            'delay',
            'date_from',
            'date_to',
            'file',
        ]);
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getData(): array
    {
        $data = $this->getFormData();
        $data['user_id'] = $this->userId;

        return $data;
    }
}
