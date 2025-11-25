<?php

namespace Modules\Document\Services;

use App\Services\Transformers\IndexDocumentsDataTransformer;
use App\Services\Transformers\ManageDocument\ShowDocumentDataTransformer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Modules\Document\DTO\UpdateDocumentDTO;
use Modules\Document\Models\Document;

class ManageDocumentService
{
    /**
     * @return Collection
     */
    public function get(): Collection
    {
        return collect([]);
//        return Document::with(['user', 'reads'])
//            ->forManager($user)
//            ->get()
//            ->map(
//                fn(Document $document) => IndexDocumentsDataTransformer::transform($document, $user)
//            );
    }

    /**
     * @param UpdateDocumentDTO $dto
     * @return Document
     */
    public function update(UpdateDocumentDTO $dto): Document
    {
        return $dto->getModel();
//        $file = $dto->getFile();
//        $dataArray = $dto->getFormData();
//        $document = $dto->getModel();
//
//        $document->update([
//            'name' => $dataArray['title'],
//            'description' => $dataArray['description'],
//            'date_from' => $dataArray['date_from'],
//            'date_to' => $dataArray['date_to'],
//            'declaration_message' => $dataArray['declaration'],
//            'delay' => $dataArray['delay'],
//        ]);
//
//        if ($file) {
//            $path = $this->updateFile($file, $dataArray['uuid']);
//            Storage::disk('documents')->delete($document->file_path);
//            $document->update([
//                'source_name' => $file->getClientOriginalName(),
//                'file_path' => "/{$path}",
//            ]);
//        }
//
//        $document->save();
//        return $document;
    }

    /**
     * @param Document $document
     * @return bool
     */
    public function delete(Document $document): bool
    {
        return true;
//        return $document->delete();
    }

    /**
     * @param Document $document
     * @param User $user
     * @return array
     */
    public function show(Document $document, User $user): array
    {
        return [];
//        return ShowDocumentDataTransformer::transform($document, $user);
    }

    /**
     * @param Document $document
     * @return array
     */
    public function documentUsers(Document $document): array
    {
        return [];
//        return User::with(['userDocuments'])
//            ->get()
//            ->map(fn(User $user) => [
//                'id' => $user->getKey(),
//                'name' => $user->name,
//                'assign' => $user->userDocuments->contains('document_id', $document->getKey())
//            ])
//            ->toArray();
    }

    /**
     */
    public function assignUser(): void
    {
//        if ($assign) {
//            UserDocument::firstOrCreate(
//                [
//                    'user_id' => $user->getKey(),
//                    'document_id' => $document->getKey(),
//                ],
//                ['created_by' => $changedBy->getKey()]
//            );
//        } else {
//            UserDocument::where([
//                'user_id' => $user->getKey(),
//                'document_id' => $document->getKey(),
//            ])->delete();
//        }
    }

    /**
     * @param UploadedFile $file
     * @param string $uuid
     * @return string
     */
    private function updateFile(UploadedFile $file, string $uuid): string
    {
        return '';
//        return $file->store("uploads/$uuid", 'documents');
    }
}
