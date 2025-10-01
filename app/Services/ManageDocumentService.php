<?php

namespace App\Services;

use App\Data\DTO\UpdateDocumentDTO;
use App\Models\Document;
use App\Models\DocumentRead;
use App\Models\User;
use App\Models\UserDocument;
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
     * @param Document $document
     * @return array
     */
    public function documentUsers(Document $document): array
    {
        return User::with(['userDocuments'])
            ->get()
            ->map(fn(User $user) => [
                'id' => $user->getKey(),
                'name' => $user->name,
                'assign' => $user->userDocuments->contains('document_id', $document->getKey())
            ])
            ->toArray();
    }

    /**
     * @param Document $document
     * @param User $user
     * @param bool $assign
     * @param User $changedBy
     * @return void
     */
    public function assignUser(Document $document, User $user, bool $assign, User $changedBy): void
    {
        if ($assign) {
            UserDocument::firstOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'document_id' => $document->getKey(),
                ],
                ['created_by' => $changedBy->getKey()]
            );
        } else {
            UserDocument::where([
                'user_id' => $user->getKey(),
                'document_id' => $document->getKey(),
            ])->delete();
        }
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

    /**
     * @param Document $document
     * @return array[]
     */
    public function readStatistics(Document $document): array
    {
        $totalAssigned = $this->documentAssignment($document);
        $totalRead = $this->documentReads($document);
        $totalNotRead = $totalAssigned - $totalRead;

        $readPercentage = round(
            ($totalRead / max($totalAssigned, 1)) * 100,
            2
        );
        $notReadPercentage = round(($totalNotRead / max($totalAssigned, 1)) * 100, 2);

        return [
            [
                'value' => $totalRead,
                'name' => "Read ($readPercentage %)"
            ],
            [
                'value' => $totalNotRead,
                'name' => "Not read ($notReadPercentage %)"
            ],
        ];
    }

    /**
     * @param Document $document
     * @return int
     */
    public function documentReads(Document $document): int
    {
        return DocumentRead::where('document_id', $document->getKey())->count();
    }

    /**
     * @param Document $document
     * @return int
     */
    public function documentAssignment(Document $document): int
    {
        return UserDocument::where('document_id', $document->getKey())->count();
    }

    /**
     * @param Document $document
     * @return float
     */
    public function documentReadRatio(Document $document): float
    {
        $totalAssigned = $this->documentAssignment($document);
        $totalRead = $this->documentReads($document);

        return round(
            ($totalRead / max($totalAssigned, 1)),
            2
        );
    }
}
