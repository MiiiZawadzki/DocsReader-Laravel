<?php

namespace App\Services;

use App\Data\DTO\UpdateDocumentDTO;
use App\Models\Document;
use App\Models\User;
use App\Services\Transformers\IndexDocumentsDataTransformer;
use App\Services\Transformers\ManageDocument\ShowDocumentDataTransformer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ManageDocumentService
{
    /**
     * @param User $user
     * @return Collection
     */
    public function get(User $user): Collection
    {
        return Document::with(['user', 'reads'])
            ->forManager($user)
            ->get()
            ->map(
                fn(Document $document) => IndexDocumentsDataTransformer::transform($document, $user)
            );
    }

    /**
     * @param UpdateDocumentDTO $dto
     * @return Document
     */
    public function update(UpdateDocumentDTO $dto): Document
    {
        $file = $dto->getFile();
        $dataArray = $dto->getFormData();
        $document = $dto->getModel();

        $document->update([
            'name' => $dataArray['title'],
            'description' => $dataArray['description'],
            'date_from' => $dataArray['date_from'],
            'date_to' => $dataArray['date_to'],
            'declaration_message' => $dataArray['declaration'],
            'delay' => $dataArray['delay'],
        ]);

        if ($file) {
            $path = $this->updateFile($file, $dataArray['uuid']);
            Storage::disk('documents')->delete($document->file_path);
            $document->update([
                'source_name' => $file->getClientOriginalName(),
                'file_path' => "/{$path}",
            ]);
        }

        $document->save();
        return $document;
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
     * @param UploadedFile $file
     * @param string $uuid
     * @return string
     */
    private function updateFile(UploadedFile $file, string $uuid): string
    {
        return $file->store("uploads/$uuid", 'documents');
    }
}
