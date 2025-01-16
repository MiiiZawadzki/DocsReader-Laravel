<?php

namespace App\Services;

use App\Data\DTO\CreateDocumentDTO;
use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DocumentService
{
    public function store(CreateDocumentDTO $dto): Document
    {
        $file = $dto->getFile();
        $dataArray = $dto->getFormData();
        $path = $this->saveFile($file);

        return Document::create([
            'uuid' => Str::uuid(),
            'name' => $dataArray['title'],
            'source_name' => $file->getClientOriginalName(),
            'description' => $dataArray['description'],
            'user_id' => $dto->user->getKey(),
            'file_path' => "/{$path}",
            'date_from' => $dataArray['date_from'],
            'date_to' => $dataArray['date_to'],
            'declaration_message' => $dataArray['declaration'],
            'delay' => $dataArray['delay'],
        ]);
    }

    private function saveFile(UploadedFile $file): string
    {
        return $file->store('uploads', 'documents');
    }
}
