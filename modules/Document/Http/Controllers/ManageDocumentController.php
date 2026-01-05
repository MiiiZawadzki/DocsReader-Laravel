<?php

namespace Modules\Document\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Document\DTO\UpdateDocumentDTO;
use Modules\Document\Http\Requests\DeleteRequest;
use Modules\Document\Http\Requests\Manage\AssignUserRequest;
use Modules\Document\Http\Requests\Manage\GetUsersRequest;
use Modules\Document\Http\Requests\Manage\ShowRequest;
use Modules\Document\Http\Requests\UpdateRequest;
use Modules\Document\Models\Document;
use Modules\Document\Repositories\Contracts\DocumentRepositoryInterface;
use Modules\Document\Repositories\Contracts\UserDocumentRepositoryInterface;
use Modules\Document\Services\DocumentService;
use Modules\Document\Services\ManageDocumentService;
use Modules\Document\Transformers\IndexDocumentsDataTransformer;
use Modules\Document\Transformers\ManageDocument\ShowDocumentDataTransformer;
use Modules\History\Api\HistoryApiInterface;
use Modules\User\Api\UserApiInterface;

readonly class ManageDocumentController
{
    public function __construct(
        private DocumentService                 $documentService,
        private ManageDocumentService           $manageDocumentService,
        private UserDocumentRepositoryInterface $userDocumentRepository,
        private DocumentRepositoryInterface     $documentRepository,
        private UserApiInterface                $userApi,
        private HistoryApiInterface             $historyApi,
    )
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();

            $documents = $this->documentService->getForManager($userId);
            $authorIds = $documents->pluck('user_id')->unique()->toArray();
            $authorTags = $this->userApi->getUsersName($authorIds);

            $result = $documents->map(
                fn(Document $document) => IndexDocumentsDataTransformer::transform(
                    $document,
                    collect(),
                    $authorTags
                )
            );

            return response()->json([
                'documents' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param ShowRequest $request
     * @return JsonResponse
     */
    public function show(ShowRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $documentUuid = $request->route('document');

            $document = $this->documentService->getDocumentByUuid($documentUuid);

            $readStatus = $this->historyApi->getReadStatusForDocument($userId, $document->getKey());
            $authorTag = $this->userApi->getUserName($document->getAttribute('user_id'));

            $documentData = ShowDocumentDataTransformer::transform(
                $document,
                $readStatus->createdAt,
                $authorTag
            );

            return response()->json([
                'document' => $documentData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        try {
            $documentUuid = $request->route('document');
            $dto = new UpdateDocumentDTO($documentUuid, $request);

            $document = $this->manageDocumentService->update($dto);

            return response()->json([
                'message' => __('document::messages.update.success', ['name' => $document->getAttribute('name')]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get users for specified resource.
     */
    public function users(GetUsersRequest $request): JsonResponse
    {
        try {
            $documentUuid = $request->route('document');
            $document = $this->documentService->getDocumentByUuid($documentUuid);

            $allUsers = $this->userApi->getAllUsers();
            $assignedUserIds = $this->userDocumentRepository->getAssignedUserIds($document->getKey());

            $users = $allUsers->map(function ($user) use ($assignedUserIds) {
                return [
                    'id' => $user->getKey(),
                    'name' => $user->getAttribute('name'),
                    'assign' => in_array($user->getKey(), $assignedUserIds),
                ];
            })->toArray();

            return response()->json([
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Assign user for specified resource.
     */
    public function userAssignment(AssignUserRequest $request): JsonResponse
    {
        try {
            $documentUuid = $request->route('document');
            $userId = intval($request->route('user'));
            $assign = $request->boolean('assign');
            $changedById = Auth::id();

            $document = $this->documentService->getDocumentByUuid($documentUuid);

            $this->manageDocumentService->assignUser(
                document: $document,
                userId: $userId,
                assign: $assign,
                changedById: $changedById
            );

            return response()->json([
                'message' => __('document::messages.manage.userAssignment.success')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete specified resource.
     */
    public function delete(DeleteRequest $request): JsonResponse
    {
        try {
            $documentUuid = $request->route('document');

            $this->documentRepository->delete($documentUuid);

            return response()->json([
                'message' => __('document::messages.manage.delete.success')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
