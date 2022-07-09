<?php

namespace App\Services\Implementations;


use App\Contracts\Responses\ReconciliationResponse;
use app\Helpers\HelperFunctions;
use App\Http\Requests\ReconciliationRequest;
use App\Jobs\ReconJob;
use App\Models\ReconRequest;
use App\Services\Interfaces\ICReportService;
use App\Services\Interfaces\IReconciliationService;
use DB;


class CReportService implements ICReportService
{

    public ReconciliationResponse $response;
    public IReconciliationService $service;
    public function __construct(IReconciliationService $service)
    {
        $this->response = new ReconciliationResponse();
        $this->service = $service;
    }

    public function initialiseRecon(ReconciliationRequest $request): ReconciliationResponse
    {
        $requestedBy = getLoggedInStaffId();
        $coverage = 'bank';
        $channel = $request->channel;
        $tranDate = $request->tranDate;
        $solId = '';
        $region = '';

        //Todo check if recon has already been done
        if(checkDuplicateRecon($coverage, $tranDate, $solId , $region)){
            $this->response->responseCode = "119";
            $this->response->responseMessage = "Reconciliation already Initiated, Check the report";
            return $this->response;
        }

        $batchNumber = HelperFunctions::generateBatchNumber();

        ReconJob::dispatch($batchNumber, $channel, $tranDate, $requestedBy, $this->service)->delay(now()->addMinutes(1));

        $this->response->batchNumber = $batchNumber;
        $this->response->isSuccessful = true;
        $this->response->responseCode = "000";
        $this->response->responseMessage = "Request Successful";
        return $this->response;
    }



}
