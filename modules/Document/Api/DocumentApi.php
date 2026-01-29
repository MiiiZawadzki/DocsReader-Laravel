<?php

namespace Modules\Document\Api;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Document\DTO\DocumentDTO;
use Modules\Document\Models\Document;
use Modules\Document\Repositories\Contracts\DocumentRepositoryInterface;
use Modules\Document\Repositories\Contracts\UserDocumentRepositoryInterface;

readonly class DocumentApi implements DocumentApiInterface
{
    public function __construct(
        private UserDocumentRepositoryInterface $userDocumentRepository,
        private DocumentRepositoryInterface     $documentRepository
    )
    {
    }

    /**
     * @param int $documentId
     * @return int
     */
    public function getAssignedUsersCount(int $documentId): int
    {
        return count($this->userDocumentRepository->getAssignedUserIds($documentId));
    }

    /**
     * @param string $documentUuid
     * @return DocumentDTO|null
     */
    public function getDocumentByUuid(string $documentUuid): ?DocumentDTO
    {
        $model = $this->documentRepository->getByUuid($documentUuid);

        if ($model) {
            return DocumentDTO::fromModel($model);
        }

        return null;
    }

    /**
     * @param array $documentsId
     * @return Collection<int, DocumentDTO>
     */
    public function getDocumentsById(array $documentsId): Collection
    {
        return $this->documentRepository->getDocumentsById($documentsId)
            ->map(fn(Document $document) => DocumentDTO::fromModel($document));
    }

    /**
     * @param int $userId
     * @return Collection<int, DocumentDTO>
     */
    public function getManagerDocuments(int $userId): Collection
    {
        return $this->documentRepository->getForManager($userId)
            ->map(fn(Document $document) => DocumentDTO::fromModel($document));
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getAssignedDocumentsCount(int $userId): int
    {
        return count($this->userDocumentRepository->getAssignedDocuments($userId));
    }

    /**
     * @param int $userId
     * @param string $documentUuid
     * @return bool
     */
    public function verifyAssignedDocument(int $userId, string $documentUuid): bool
    {
        $document = $this->documentRepository->getByUuid($documentUuid);

        return $this->userDocumentRepository->getAssignedDocuments($userId)->contains('document_id', $document->id);
    }

    /**
     * @param int $userId
     * @param Carbon $date
     * @return int
     */
    public function getAssignedDocumentsCountForDate(int $userId, Carbon $date): int
    {
        return count($this->userDocumentRepository->getAssignedDocumentsCountForDate($userId, $date));
    }

    /**
     * @param int $userId
     * @param Carbon $date
     * @return int
     */
    public function getCreatedDocumentsCountForDate(int $userId, Carbon $date): int
    {
        return count($this->documentRepository->getCreatedDocumentsForDate($userId, $date));
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getCreatedDocumentsCount(int $userId): int
    {
        return count($this->documentRepository->getForManager($userId));
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getUsersForCreatedDocumentsCount(int $userId): int
    {
        $managerDocuments = $this->documentRepository->getForManager($userId);

        return count(
            $this->userDocumentRepository->getAssignedForDocuments($managerDocuments->pluck('id')->toArray())
        );
    }
}
