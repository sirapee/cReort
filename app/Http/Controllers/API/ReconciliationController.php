<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReconciliationRequest;
use App\Services\Interfaces\ICReportService;

class ReconciliationController extends Controller
{
    public ICReportService $service;
    public function __construct(ICReportService $service)
    {
        $this->service = $service;
        $this->middleware('auth:api');
    }

    public function initiate(ReconciliationRequest $request): \Illuminate\Http\JsonResponse
    {
        $response =$this->service->initialiseRecon($request);
        if($response->isSuccessful){
            return response()->json($response, 201);
        }
        return response()->json($response, 400);
    }
}
