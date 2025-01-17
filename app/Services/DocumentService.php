<?php

namespace App\Services;

use App\Data\DTO\CreateDocumentDTO;
use App\Models\Document;
use App\Models\User;
use App\Services\Transformers\IndexDocumentsDataTransformer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * @param User $user
     * @return Collection
     */
    public function get(User $user): Collection
    {
        return Document::with(['user', 'reads'])
            ->forUser($user)
            ->get()
            ->map(
                fn(Document $document) => IndexDocumentsDataTransformer::transform($document, $user)
            );
    }

    /**
     * @param CreateDocumentDTO $dto
     * @return Document
     */
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

    /**
     * @param UploadedFile $file
     * @return string
     */
    private function saveFile(UploadedFile $file): string
    {
        return $file->store('uploads', 'documents');
    }
}
