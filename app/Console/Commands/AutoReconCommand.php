<?php

namespace App\Console\Commands;

use App\Helpers\CheckTrustedUsers;
use app\Helpers\HelperFunctions;
use App\Services\Interfaces\IReconciliationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;

class AutoReconCommand extends Command
{
    public IReconciliationService $service;
    public function __construct(IReconciliationService $service)
    {
        parent::__construct();
        $this->service = $service;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recon:auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatic Reconciliation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{

            $tranDate = Carbon::today()->addDay(-2);
            Log::info("Validating Recon for $tranDate transactions...");
            $this->info("Validating Recon for $tranDate transactions...");
            $tranDatePlus = Carbon::today()->addDay(-1);
            if(!validateReconAndProceed($tranDate, $tranDatePlus)){
                Log::info("Validation Failed, Recon Stopped for $tranDate");
                $this->error("Validation Failed, Recon Stopped for $tranDate");
                exit();
            }

            $this->info("Initiating Recon for $tranDate transactions, this might take several minutes");
            $this->info('Please Wait.....');
            $batchNumber = HelperFunctions::generateBatchNumber();
            $result =$this->service->initiateRecon($tranDate, $batchNumber, 'SYSTEM', 'ATM');
            if($result->isSuccessful){
                $this->info( $result->responseMessage);
            }else{
                $this->error( $result->responseMessage);
            }
            $this->info('Processing Complete!!!');


        }catch (Exception $e){
            Log::info($e->getMessage());
            $this->error('Process failed');
            $this->error($e->getMessage());
        }/**/
        return 0;
    }
}
