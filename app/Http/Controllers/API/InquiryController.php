<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coverage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth:api');
    }

    public function regions (): JsonResponse
    {
        return response()->json(getRegions());
    }

    public function sols (): JsonResponse
    {
        return response()->json(getSols());
    }

    public function solsByRegion ($region): JsonResponse
    {
        return response()->json(getSolByRegions($region));
    }

    public function coverage(): JsonResponse
    {
        return response()->json(Coverage::allCoverage());
    }

}
