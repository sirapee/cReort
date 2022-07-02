<?php

namespace App\Services\Implementations;

use App\Contracts\Responses\ReconciliationResponse;
use App\Http\Requests\ReconciliationRequest;

interface IReconciliationService
{
    public function initialiseRecon(ReconciliationRequest $request): ReconciliationResponse;
}
