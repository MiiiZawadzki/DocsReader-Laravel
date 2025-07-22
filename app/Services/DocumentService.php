<?php

namespace App\Services;

use App\Data\DTO\CreateDocumentDTO;
use App\Models\Document;
use App\Models\DocumentRead;
use App\Models\User;
use App\Services\Transformers\IndexDocumentsDataTransformer;
use App\Services\Transformers\ShowDocumentDataTransformer;
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
        $uuid = Str::uuid();
        $file = $dto->getFile();
        $dataArray = $dto->getFormData();
        $path = $this->saveFile($file, $uuid);

        return Document::create([
            'uuid' => $uuid,
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
     * @param Document $document
     * @param User $user
     * @return array
     */
    public function show(Document $document, User $user): array
    {
        return ShowDocumentDataTransformer::transform($document, $user);
    }

    /**
     * @param Document $document
     * @param User $user
     * @return DocumentRead
     */
    public function markRead(Document $document, User $user): DocumentRead
    {
        return DocumentRead::firstOrCreate([
            'document_id' => $document->getKey(),
            'user_id' => $user->getKey(),
            'confirmed' => true
        ]);
    }

    /**
     * @param UploadedFile $file
     * @param string $uuid
     * @return string
     */
    private function saveFile(UploadedFile $file, string $uuid): string
    {
        return $file->store("uploads/$uuid", 'documents');
    }
}
