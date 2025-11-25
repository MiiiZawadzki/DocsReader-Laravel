<?php

namespace Modules\Document\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Http\Requests\DeleteRequest;
use Modules\Document\Http\Requests\Manage\AssignUserRequest;
use Modules\Document\Http\Requests\Manage\GetUsersRequest;
use Modules\Document\Http\Requests\Manage\ShowRequest;
use Modules\Document\Http\Requests\UpdateRequest;
use Modules\Document\Services\ManageDocumentService;

class ManageDocumentController
{
    public function __construct(private readonly ManageDocumentService $documentService)
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
            $documents = $this->documentService->get(Auth::user());
            return response()->json([
                'documents' => $documents,
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
//            $document = Document::where('uuid', $request->route('document'))->first();
//            $documentData = $this->documentService->show($document, Auth::user());
//            return response()->json([
//                'document' => $documentData,
//            ]);
            return response()->json([
                'document' => [],
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
//            $document = $this->documentService->update(new UpdateDocumentDTO($request->route('document'), $request));
//            return response()->json([
//                'message' => __('api.document.update.success', ['name' => $document->getAttribute('name')]),
//            ]);
            return response()->json([
                'message' => __('api.document.update.success', ['name' => 'asd']),
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
//        $document = Document::where('uuid', $request->route('document'))->first();

        try {
//            return response()->json([
//                'users' => $this->documentService->documentUsers($document)
//            ]);
            return response()->json([
                'users' => []
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
//            $document = Document::where('uuid', $request->route('document'))->first();
//            $user = User::find(intval($request->route('user')));
//
//            $this->documentService->assignUser(
//                document: $document,
//                user: $user,
//                assign: $request->boolean('assign'),
//                changedBy: Auth::user()
//            );
//
//            return response()->json([
//                'message' => __('api.document.manage.userAssignment.success')
//            ]);
            return response()->json([
                'message' => __('api.document.manage.userAssignment.success')
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
//            $document = Document::where('uuid', $request->route('document'))->first();
//
//            $this->documentService->delete($document);
//
//            return response()->json([
//                'message' => __('api.document.manage.delete.success')
//            ]);
            return response()->json([
                'message' => __('api.document.manage.delete.success')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
