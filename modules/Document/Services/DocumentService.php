<?php

namespace Modules\Document\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Document\DTO\CreateDocumentDTO;
use Modules\Document\Models\Document;
use Modules\Document\Repositories\Contracts\DocumentRepositoryInterface;

class DocumentService
{
    public function __construct(private readonly DocumentRepositoryInterface $repository)
    {
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getForUser(int $userId): Collection
    {
        return $this->repository->getForUser($userId);
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
     * @param string $documentUuId
     * @return Document
     */
    public function getDocumentByUuid(string $documentUuId): Document
    {
        return $this->repository->getByUuid($documentUuId);
    }

    /**
     * @param CreateDocumentDTO $dto
     * @return Document
     */
    public function store(CreateDocumentDTO $dto): Document
    {
        $uuid = Str::uuid();
        $file = $dto->getFile();
        $dataArray = $dto->getData();
        $path = $this->saveFile($file, $uuid);

        return $this->repository->create([
            'uuid' => $uuid,
            'name' => $dataArray['title'],
            'source_name' => $file->getClientOriginalName(),
            'description' => $dataArray['description'],
            'user_id' => $dataArray['user_id'],
            'file_path' => "/{$path}",
            'date_from' => $dataArray['date_from'],
            'date_to' => $dataArray['date_to'],
            'declaration_message' => $dataArray['declaration'],
            'delay' => $dataArray['delay'],
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
