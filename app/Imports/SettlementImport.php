<?php

namespace App\Imports;

use App\Models\NibssSettlement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Throwable;

class SettlementImport implements ToCollection ,  SkipsOnError
{
    Use Importable, SkipsErrors;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function collection(Collection $collection)
    {
        foreach ($collection as $row)
        {
            $status = 'Failed';
            $batchNumber = Cache::get('batchNumber');
            $requestedBy = Cache::get('requestedBy');
            $type = Cache::get('type');
            if (!isset($row[2]))
                continue;
            if(strlen($row[2]) < 30)
                continue;
            $channel = trim($row[1]);
            $sessionId = trim($row[2], "'");
            $transactionType= trim($row[3]);
            $response = trim($row[4]);
            $amount = trim($row[5]);
            $transactionTime = trim($row[6], "'");
            $sourceInstitution =  trim($row[7]);
            $senderName =  trim($row[8]);
            $destinationBank = trim($row[9]);
            $destinationAccountName = trim($row[10]);
            $destinationAccountNumber =  trim($row[11]);
            $narration = trim($row[12]);
            $paymentReference =  trim($row[13], "'");

            $strippedResponse = strtolower(str_replace(' ', '', $response));
            if(NibssSettlement::where('BatchNumber', $batchNumber)->where('SessionId', $sessionId)->exists()){
                continue;
            }

            if(str_contains($strippedResponse, 'approved') || str_contains($strippedResponse, 'completedsuccessfully')){
                Log::info("Approved");
                $status = 'Successful';
                Log::info($response . ' => '.$sessionId . ' => '. $amount . ' => '. $destinationAccountNumber . ' => '. $narration . ' => '. $destinationAccountName);
                //Todo Log
                NibssSettlement::create([
                    'Channel' => $channel,
                    'SessionId' => $sessionId,
                    'TransactionType' => $transactionType,
                    'Response' => $response,
                    'Amount' =>$amount,
                    'TransactionTime' => $transactionTime,
                    'SourceInstitution' => $sourceInstitution,
                    'SenderName' => $senderName,
                    'DestinationBank' => $destinationBank,
                    'DestinationAccountName' => $destinationAccountName,
                    'DestinationAccountNumber' => $destinationAccountNumber,
                    'Narration' => $narration,
                    'paymentReference' => $paymentReference,
                    'Direction' => $type,
                    'BatchNumber' => $batchNumber,
                    'Status' => $status,
                    'RequestedBy' => $requestedBy,
                    'RequestDate' => Carbon::today()
                ]);/**/
            }else{
                Log::info($response);
                Log::info($response . ' => '.$sessionId . ' => '. $amount . ' => '. $destinationAccountNumber . ' => '. $narration . ' => '. $destinationAccountName);
                //Todo Log
            }


        }
    }


    public function onError(Throwable $e)
    {
        // TODO: Implement onError() method.
    }
}
