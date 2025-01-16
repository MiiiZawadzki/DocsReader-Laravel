<?php

namespace App\Data\DTO;

use App\Http\Requests\Api\Document\StoreRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class CreateDocumentDTO
{
    public User $user;

    public function __construct(private readonly StoreRequest $request)
    {
        $this->user = Auth::user();
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
}
