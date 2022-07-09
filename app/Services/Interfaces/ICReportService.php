<?php

namespace App\Services\Interfaces;

use App\Contracts\Responses\ReconciliationResponse;
use App\Http\Requests\ReconciliationRequest;

interface ICReportService
{
    public function initialiseRecon(ReconciliationRequest $request): ReconciliationResponse;
}
