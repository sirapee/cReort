<?php

namespace App\Jobs;

use App\Contracts\Responses\DefaultResponse;
use App\Events\BulkWalletGenerateStatusUpdate;
use App\Models\AllReconData;
use App\Models\BulkWalletInfo;
use App\Models\ReconRequest;
use App\Services\Interfaces\IReconciliationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use DB;

class ReconJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tranDate;
    public $channel;
    public $batchNumber;
    public $requestedBy;
    public $service;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($batchNumber, $channel, $tranDate, $requestedBy, $service)
    {
        $this->channel = $channel;
        $this->tranDate = $tranDate;
        $this->batchNumber = $batchNumber;
        $this->service = $service;
        $this->requestedBy = $requestedBy;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try{
            $result =$this->service->initiateRecon($this->tranDate, $this->batchNumber, $this->requestedBy, $this->channel);
            Log::info('Result  '. json_encode($result));
            if($result->isSuccessful){
                Log::info('Result  '. $result->responseMessage);
            }else{
                Log::error('Result  '. $result->responseMessage);
            }
            Log::info('Processing Complete!!!');

            Log::info('Sending Update Notification');
            //broadcast(new BulkWalletGenerateStatusUpdate($status, $this->merchantId));
        }catch (\Exception $e){
            Log::error($e->getMessage());
            Log::error($e);

            //broadcast(new BulkWalletGenerateStatusUpdate($status, $this->merchantId));
        }
    }
}
