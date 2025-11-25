<?php

namespace Modules\Document\DTO;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Http\Requests\StoreRequest;

class CreateDocumentDTO
{
//    public User $user;

    public function __construct(private readonly StoreRequest $request)
    {
//        $this->user = Auth::user();
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
