<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Dashboard\Services\HomeService;

class HomeController
{
    public function __construct(private readonly HomeService $homeService)
    {
    }

    /**
     * @return JsonResponse
     */
    public function data(): JsonResponse
    {
        try {
            $user = Auth::user(); //->load(['userDocuments', 'userDocuments.document', 'userDocuments.document.reads']);

            $userData = $this->homeService->data($user);
            return response()->json([
                'data' => $userData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
