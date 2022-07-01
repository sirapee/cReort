<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Http\Requests\SettlementRarRequest;
use App\Services\Interfaces\ISettlementUploadService;
use Illuminate\Support\Facades\Log;

class SettlementController extends Controller
{
    private ISettlementUploadService $uploadService;
    public function __construct(ISettlementUploadService $settlementUploadService)
    {
        $this->uploadService = $settlementUploadService;
       // $this->middleware('auth:api', ['except' => ['register']]);
    }
    public function uploadAndExtractSettlementFiles(SettlementRarRequest $request): \Illuminate\Http\JsonResponse
    {
        $response = $this->uploadService->uploadSettlementRarFile($request);
        if(!$response->isSuccessful) {
            return response()->json($response, 400);
        }
        return response()->json($response);

    }
}
