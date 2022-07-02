<?php

namespace App\Services\Implementations;


use App\Contracts\Responses\ReconciliationResponse;
use App\Http\Requests\ReconciliationRequest;
use App\Models\User;


class ReconciliationService implements IReconciliationService
{

    public ReconciliationResponse $response;

    public function __construct()
    {
        $this->response = new ReconciliationResponse();
    }

    public function initialiseRecon(ReconciliationRequest $request): ReconciliationResponse
    {

        return $this->response;
    }

}
