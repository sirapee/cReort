<?php

namespace App\Services\Implementations;


use App\Contracts\Responses\ReconciliationResponse;
use app\Helpers\HelperFunctions;
use App\Http\Requests\ReconciliationRequest;
use App\Models\AllReconData;
use App\Models\ReconEntry;
use App\Models\ReconRequest;
use App\Models\ReversedEntry;
use App\Models\SettledEntry;
use App\Models\SettlementEntry;
use App\Models\UnImpactedEntry;
use App\Services\Interfaces\IReconciliationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use DB;


class ReconciliationService implements IReconciliationService
{

    public ReconciliationResponse $response;

    public function __construct()
    {
        $this->response = new ReconciliationResponse();
    }

    public function initiateRecon($reconDate, $batchNumber, $requestedBy, $channel= ''): ReconciliationResponse
    {
        $date = Carbon::createFromFormat('Y-m-d',  $reconDate);
        $tranDate = Carbon::parse($date)->format('Y-m-d');
        if(checkDuplicateRecon('bank', $tranDate)){
            $this->response->responseCode = "119";
            $this->response->responseMessage = "Reconciliation already Initiated, Check the report";
            return $this->response;
        }

        DB::beginTransaction();
        Log::info("Creating a recon request record...");
        ReconRequest::create([
            'BatchNumber' => $batchNumber,
            'Coverage' => 'bank',
            'SolId' => '',
            'Region' => '',
            'TranDate' => $tranDate,
            'RequestedBy' => $requestedBy
        ]);
        Log::info("Done Creating a recon request record...");

        Log::info("Fetching data from post office...");
        //$data = DB::table('postilion_data')->where('TranType', '1')->where('ResponseCode', '0')->orderBy('id')->limit(1000)->get();
        Log::info("Done Fetching data from post office...");
        Log::info("Processing data from post office...");
        DB::table('post_office_atm_transactions')
            ->whereDate('DateLocal', $tranDate)
            ->where('TranType', '1')->where('ResponseCode', '0')
            ->orderBy('id')->lazy()->each(function ($postTran) use ($batchNumber, $requestedBy) {

                $solId = substr($postTran->TerminalIdPostilion, 4,3);
                $regionObj = geRegionBySol($solId);
                $region = $regionObj->region ?? 'No Region';
                $status = 'Successful';
                $this->allRecordsInsert($postTran, $batchNumber, $solId, $region, $status, $requestedBy);
        });

        Log::info("Done Processing data from post office...");
        //Check for reversed transactions
        Log::info("Processing Reversal data from post office...");
        DB::table('all_recon_data')->where('MessageType', '420')->orderBy('id')->chunk(100, function ($tranData) {
            foreach ($tranData as $recon) {
                $origDetails = getOriginalDetails($recon->TerminalIdPostilion, $recon->RetrievalReferenceNumberPostilion, $recon->StanPostilion, $recon->Pan);
                //Log::info(json_encode($origDetails, JSON_THROW_ON_ERROR));
                if(!empty($origDetails)){
                    DB::table('all_recon_data')
                        ->where('id', $origDetails->Id)
                        ->update(['Status' => 'Reversed','IsReversed' => true, 'ReversalId' => $recon->Id, 'updated_at' => Carbon::now()]);

                    ReversedEntry::create([
                        'Pan' => $origDetails->Pan,
                        'RequestDate' => $origDetails->RequestDate,
                        'AccountNumber' => $origDetails->AccountNumber,
                        'DateLocal' => $origDetails->DateLocal,
                        'ResponseCode' => $origDetails->ResponseCode,
                        'RetrievalReferenceNumberPostilion' => $origDetails->RetrievalReferenceNumberPostilion,
                        'StanPostilion' => $origDetails->StanPostilion,
                        'TranType' => $origDetails->TranType,
                        'AmountPostilion' => $origDetails->AmountPostilion,
                        'TerminalIdPostilion' => $origDetails->TerminalIdPostilion,
                        'MessageType' => $origDetails->MessageType,
                        'IssuerName' => $origDetails->IssuerName,
                        //'TransactionType1' => $origDetails->TransactionType1,
                        'BatchNumber' => $origDetails->BatchNumber,
                        'SolId' => $origDetails->SolId,
                        'Region' => $origDetails->Region,
                        'Status' => $origDetails->Status,
                        'TriggeredBy' => $origDetails->TriggeredBy,
                        'IsReversed' => true,
                        'ReversalId' => $recon->Id,
                        'updated_at' => Carbon::now()
                    ]);


                }

            }
        });

        Log::info("Done Processing Reversal data from post office...");

        Log::info("Fetching and processing data from finacle...");

        //Formatting finacle dates
        $startDate = Carbon::parse($date->addDays(-5))->format('Y-m-d');
        $endDate = Carbon::parse($date->addDays(5))->format('Y-m-d');
        $entryDate = Carbon::parse($date)->format('Y-m-d');
        DB::connection('oracle')->table('finacle_atm_transactions')
            ->whereDate('entrydate', $entryDate)
            ->whereBetween('trandatefinacle', [$startDate, $endDate])
            ->orderBy('tranIdFinacle')->chunk(100, function ($finacleData) use($batchNumber, $requestedBy) {
            foreach ($finacleData as $fin) {
                //Log::info($fin->tranremarks);
                $tranRemarksArray = explode('/',$fin->tranremarks);
                //Log::info(json_encode($tranRemarksArray));
                $terminalId = '';
                $solId = '999';
                $terminalType = '';
                if(array_key_exists(0, $tranRemarksArray)){
                    $terminalId = trim($tranRemarksArray[0]);
                    $solId = substr($terminalId, 4, 3);
                    $terminalType = $terminalId[0];
                }
                $stan = '';
                if(array_key_exists(1, $tranRemarksArray)){
                    $stan = trim($tranRemarksArray[1]);
                }

                $rrn = '';
                if(array_key_exists(1, $tranRemarksArray)){
                    $rrn = trim($tranRemarksArray[2]);
                }

                //Log::info("$rrn $stan $solId");
                if($this->postOfficeFinacleCheck($rrn, $stan, $terminalId, $fin)){
                    //Log::info("Exists $rrn $stan $terminalId");
                    $this->updateAllDataWithFinacleData($rrn, $stan, $terminalId, $fin);
                }else{
                    //Todo Insert into Settlement Entries Table
                    $this->insertSettlementEntry($batchNumber, $fin, $stan, $solId, $rrn, $terminalId, $requestedBy, $terminalType);
                }

            }
        });
        Log::info("Done Processing data from post office...");

        Log::info("Processing Unimpacted transactions...");
        DB::table('all_recon_data')->where('MessageType', '200')->where('IsReversed', false)->where('Status', 'Successful')->orderBy('id')->chunk(100, function ($tranData) {
            foreach ($tranData as $recon) {
                      $this->insertUnImpactedData($recon);

                DB::table('all_recon_data')
                    ->where('Id', $recon->Id)
                    //->where('Pan', $fin->panfinacle)
                    ->update(
                        [
                            'Status' => 'UnImpacted',
                            'updated_at' => Carbon::now()
                        ]
                    );

            }
        });/**/
        Log::info("Done Processing Unimpacted transactions...");

        Log::info("Processing Done...");



        $this->response->batchNumber = $batchNumber;
        $this->response->isSuccessful = true;
        $this->response->responseCode = "000";
        $this->response->responseMessage = "Request Successful";
        DB::commit();
        return $this->response;
    }


    /**
     * @param mixed $postTran
     * @param string $batchNumber
     * @param string $solId
     * @param string $region
     * @param string $status
     * @param $requestedBy
     * @return void
     */
    public function allRecordsInsert(mixed $postTran, string $batchNumber, string $solId, string $region, string $status, $requestedBy): void
    {
        AllReconData::create([
            'Pan' => $postTran->Pan,
            'RequestDate' => $postTran->RequestDate,
            'AccountNumber' => $postTran->AccountNumber,
            'DateLocal' => $postTran->DateLocal,
            'ResponseCode' => $postTran->ResponseCode,
            'RetrievalReferenceNumberPostilion' => $postTran->RetrievalReferenceNumberPostilion,
            'StanPostilion' => $postTran->Stan,
            'TranType' => $postTran->TranType,
            'AmountPostilion' => $postTran->AmountPostilion,
            'TerminalIdPostilion' => $postTran->TerminalIdPostilion,
            'MessageType' => $postTran->MessageType,
            'IssuerName' => $postTran->IssuerName,
            'TransactionType1' => $postTran->TransactionType1,
            'BatchNumber' => $batchNumber,
            'SolId' => $solId,
            'Region' => $region,
            'Status' => $status,
            'TriggeredBy' => $requestedBy
        ]);
    }

    /**
     * @param string $rrn
     * @param string $stan
     * @param string $terminalId
     * @param mixed $fin
     * @return void
     */
    public function updateAllDataWithFinacleData(string $rrn, string $stan, string $terminalId, mixed $fin): void
    {
        //Log::info("Finacle Data Update");
        DB::table('all_recon_data')
            ->where('RetrievalReferenceNumberPostilion', $rrn)
            ->where('StanPostilion', $stan)
            ->where('TerminalIdPostilion', $terminalId)
            ->where('AmountPostilion', $fin->amountfinacle)
            //->where('Pan', $fin->panfinacle)
            ->update(
                [
                    'NarrationFinacle' => $fin->narrationfinacle,
                    'AccountNameFinacle' => $fin->accountnamefinacle,
                    'AccountNumberFinacle' => $fin->accountnumberfinacle,
                    'TranCurrencyFinacle' => $fin->trancurrencyfinacle,
                    'TranIdFinacle' => $fin->tranidfinacle,
                    'AmountFinacle' => $fin->amountfinacle,
                    'StanFinacle' => $stan,
                    'RetrievalReferenceNumberFinacle' => $rrn,
                    'TerminalIdFinacle' => $terminalId,
                    'Status' => 'Reconciled',
                    'updated_at' => Carbon::now()
                ]
            );/**/

        $postData = DB::table('all_recon_data')
            ->where('RetrievalReferenceNumberPostilion', $rrn)
            ->where('StanPostilion', $stan)
            ->where('TerminalIdPostilion', $terminalId)
            ->where('AmountPostilion', $fin->amountfinacle)
            //->where('Pan', $fin->panfinacle)
            ->first();

        ReconEntry::create([
            'Pan' => $postData->Pan,
            'RequestDate' => $postData->RequestDate,
            'AccountNumber' => $postData->AccountNumber,
            'DateLocal' => $postData->DateLocal,
            'ResponseCode' => $postData->ResponseCode,
            'RetrievalReferenceNumberPostilion' => $postData->RetrievalReferenceNumberPostilion,
            'StanPostilion' => $postData->StanPostilion,
            'TranType' => $postData->TranType,
            'AmountPostilion' => $postData->AmountPostilion,
            'TerminalIdPostilion' => $postData->TerminalIdPostilion,
            'MessageType' => $postData->MessageType,
            'IssuerName' => $postData->IssuerName,
            //'TransactionType1' => $recon->TransactionType1,
            'BatchNumber' => $postData->BatchNumber,
            'SolId' => $postData->SolId,
            'Region' => $postData->Region,
            'Status' => 'Reconciled',
            'TriggeredBy' => $postData->TriggeredBy,
            'NarrationFinacle' => $fin->narrationfinacle,
            'AccountNameFinacle' => $fin->accountnamefinacle,
            'AccountNumberFinacle' => $fin->accountnumberfinacle,
            'TranCurrencyFinacle' => $fin->trancurrencyfinacle,
            'TranIdFinacle' => $fin->tranidfinacle,
            'AmountFinacle' => $fin->amountfinacle,
            'StanFinacle' => $stan,
            'RetrievalReferenceNumberFinacle' => $rrn,
            'TerminalIdFinacle' => $terminalId,
        ]);
    }

    /**
     * @param string $batchNumber
     * @param mixed $fin
     * @param string $stan
     * @param string $solId
     * @param string $rrn
     * @param string $terminalId
     * @param $requestedBy
     * @return void
     */
    public function insertSettlementEntry(string $batchNumber, mixed $fin, string $stan, string $solId, string $rrn, string $terminalId, $requestedBy, $terminalType): void
    {
        $regionObj = geRegionBySol($solId);
        $region = $regionObj->region ?? 'No Region';
        $data = [
            'BatchNumber' => $batchNumber,
            'PanFinacle' => $fin->panfinacle,
            'NarrationFinacle' => $fin->narrationfinacle,
            'AccountNameFinacle' => $fin->accountnamefinacle,
            'AccountNumberFinacle' => $fin->accountnumberfinacle,
            'TranCurrencyFinacle' => $fin->trancurrencyfinacle,
            'TranIdFinacle' => $fin->tranidfinacle,
            'AmountFinacle' => $fin->amountfinacle,
            'StanFinacle' => $stan,
            'EntryDate' => $fin->entrydate,
            'SolId' => $solId,
            'RetrievalReferenceNumberFinacle' => $rrn,
            'TerminalIdFinacle' => $terminalId,
            'Status' => 'PendingSettlement',
            'TriggeredBy' => $requestedBy,
            'Region' => $region,
            'TerminalType' => $terminalType,
            'ValueDateFinacle' => $fin->valuedatefinacle,
            'TranDateFinacle' => $fin->trandatefinacle,
        ];
        SettlementEntry::create($data);
    }

    /**
     * @param mixed $recon
     * @return void
     */
    public function insertUnImpactedData(mixed $recon): void
    {
        UnImpactedEntry::create([
            'Pan' => $recon->Pan,
            'RequestDate' => $recon->RequestDate,
            'AccountNumber' => $recon->AccountNumber,
            'DateLocal' => $recon->DateLocal,
            'ResponseCode' => $recon->ResponseCode,
            'RetrievalReferenceNumberPostilion' => $recon->RetrievalReferenceNumberPostilion,
            'StanPostilion' => $recon->StanPostilion,
            'TranType' => $recon->TranType,
            'AmountPostilion' => $recon->AmountPostilion,
            'TerminalIdPostilion' => $recon->TerminalIdPostilion,
            'MessageType' => $recon->MessageType,
            'IssuerName' => $recon->IssuerName,
            //'TransactionType1' => $recon->TransactionType1,
            'BatchNumber' => $recon->BatchNumber,
            'SolId' => $recon->SolId,
            'Region' => $recon->Region,
            'Status' => 'UnImpacted',
            'TriggeredBy' => $recon->TriggeredBy
        ]);
    }

    /**
     * @param string $rrn
     * @param string $stan
     * @param string $terminalId
     * @param mixed $fin
     * @return mixed
     */
    public function postOfficeFinacleCheck(string $rrn, string $stan, string $terminalId, mixed $fin) : bool
    {
        //
        return DB::table('all_recon_data')
            ->where('RetrievalReferenceNumberPostilion', $rrn)
            ->where('StanPostilion', $stan)
            ->where('TerminalIdPostilion', $terminalId)
            ->where('AmountPostilion', $fin->amountfinacle)
            //->where('Pan', $fin->panfinacle)
            ->exists();
    }

}
