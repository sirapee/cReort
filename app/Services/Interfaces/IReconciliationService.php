<?php

namespace App\Services\Interfaces;

use App\Contracts\Responses\ReconciliationResponse;
use App\Http\Requests\ReconciliationRequest;

interface IReconciliationService
{
    public function initiateRecon($tranDate,$batchNumber, $requestedBy, $channel= ''): ReconciliationResponse;

}
