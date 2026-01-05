<?php

namespace Modules\Document\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Modules\Document\DTO\UpdateDocumentDTO;
use Modules\Document\Models\Document;
use Modules\Document\Repositories\Contracts\DocumentRepositoryInterface;
use Modules\Document\Repositories\Contracts\UserDocumentRepositoryInterface;

class ManageDocumentService
{
    public function __construct(
        private readonly DocumentRepositoryInterface $repository,
        private readonly UserDocumentRepositoryInterface $userDocumentRepository
    )
    {
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getForManager(int $userId): Collection
    {
        return $this->repository->getForManager($userId);
    }

    /**
     * @param UpdateDocumentDTO $dto
     * @return Document
     */
    public function update(UpdateDocumentDTO $dto): Document
    {
        $document = $dto->getModel();
        $file = $dto->getFile();
        $formData = $dto->getFormData();

        $updateData = [
            'name' => $formData['title'],
            'description' => $formData['description'],
            'date_from' => $formData['date_from'],
            'date_to' => $formData['date_to'],
            'declaration_message' => $formData['declaration'],
            'delay' => $formData['delay'],
        ];

        if ($file) {
            $path = $this->updateFile($file, $formData['uuid']);
            $oldFilePath = $document->file_path;

            $updateData['source_name'] = $file->getClientOriginalName();
            $updateData['file_path'] = "/$path";

            if ($oldFilePath) {
                Storage::disk('documents')->delete($oldFilePath);
            }
        }

        return $this->repository->update($document, $updateData);
    }

    /**
     * @param Document $document
     * @param int $userId
     * @param bool $assign
     * @param int $changedById
     * @return void
     */
    public function assignUser(Document $document, int $userId, bool $assign, int $changedById): void
    {
        if ($assign) {
            $this->userDocumentRepository->assignUser(
                $document->getKey(),
                $userId,
                $changedById
            );
        } else {
            $this->userDocumentRepository->unassignUser(
                $document->getKey(),
                $userId
            );
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
}
