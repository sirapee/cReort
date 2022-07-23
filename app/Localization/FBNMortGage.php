<?php

namespace App\Localization;

use App\Models\NibssPostingEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use DB;

class FBNMortGage
{

    public function processNibssInward($tranDate, $batchNumber, $requestedBy): void
    {
        $inwardGl =  env('NIBSS_INWARD_GL');;
        DB::connection('oracle-fbnm')->table('NibssInwardTranBankFbnM')
            ->where('entrydate', $tranDate)
            ->orderBy('Id')->chunk(1000, function ($inwardData) use ($batchNumber, $requestedBy, $inwardGl) {
                foreach ($inwardData as $data) {
                    $sessionId = '';
                    $nameInquiryRef = '';
                    $sourceInstitution = '';
                    $amount = 0;
                    $c24Resp = '';
                    $entryDate = '';
                    $beneficiaryAccountNumber = '';
                    $beneficiaryAccountName = '';
                    $sessionId = $data->sessionid;
                    $nameInquiryRef = $data->nameinquiryref;
                    $sourceInstitution = $data->sourceinstitution;
                    $amount = $data->amount;
                    $c24Resp = $data->c24resp;
                    $entryDate = $data->entrydate;
                    $beneficiaryAccountNumber = $data->beneficiaryaccountnumber;
                    $beneficiaryAccountName = $data->beneficiaryaccountname;

                    if(checkNibssInward($batchNumber, $sessionId, $amount)){
                        DB::table('nibss_settlements')->where('Amount', $amount)
                            ->where('BatchNumber', $batchNumber)
                            ->where('SessionId', $sessionId)
                            ->where('Direction', 'Inward')
                            ->update([
                                'SessionIdBank' => $sessionId,
                                'AmountBank' => $amount,
                                'SourceInstitutionBank' => $sourceInstitution,
                                'DestinationAccountNameBank' => $beneficiaryAccountName,
                                'DestinationAccountNumberBank' => $beneficiaryAccountNumber,
                                'EntryDateBankBank' => $entryDate,
                                'Status' => 'Reconciled',
                                'updated_at' => Carbon::now()
                            ]);
                    }else{
                        //Todo reversed wrong credit
                        $narration = 'REV ' . $sessionId; //TBD
                        NibssPostingEntry::create([
                            'BatchNumber' => $batchNumber,
                            'SessionId' => $sessionId, 'Amount' => $amount,
                            'TransactionTime' => $entryDate,'SourceInstitution' => $sourceInstitution,
                            'DestinationAccountName' => $beneficiaryAccountName, 'EntryDate' => $entryDate,
                            'DestinationAccountNumber' => $beneficiaryAccountNumber,
                            'DebitAccountNumber' => $beneficiaryAccountNumber,
                            'CreditAccountNumber' => $inwardGl,
                            'PostingAmount' => $amount, 'TranType' => 'Reversal',
                            'Narration' => $narration, 'RequestedBy' => $requestedBy
                        ]);
                    }
                    Log::info(json_encode($data));
                }
            });

        DB::table('nibss_settlements')
            ->where('BatchNumber', $batchNumber)
            ->where('Status', 'Successful')
            ->where('Direction', 'Inward')
            ->orderBy('Id')->chunk(1000, function ($nibbsData) use ($batchNumber, $requestedBy, $inwardGl) {
                foreach ($nibbsData as $data) {
                    //Todo settlement
                    NibssPostingEntry::create([
                        'BatchNumber' => $batchNumber,
                        'SessionId' => $data->SessionId, 'Amount' => $data->Amount,
                        'TransactionTime' => $data->TransactionTime,'SourceInstitution' => $data->SourceInstitution,
                        'DestinationAccountName' => $data->DestinationAccountName, 'EntryDate' => $data->TransactionTime,
                        'DestinationAccountNumber' => $data->DestinationAccountNumber,
                        'DebitAccountNumber' => $inwardGl,
                        'CreditAccountNumber' => $data->DestinationAccountNumber,
                        'PostingAmount' => $data->Amount, 'TranType' => 'UnImpacted',
                        'Narration' => $data->Narration,
                        'SenderName' => $data->SenderName,
                        'RequestedBy' => $requestedBy
                    ]);
                    DB::table('nibss_settlements')->where('id', $data->id)
                        ->update([
                            'Status' => 'UnImpacted',
                            'updated_at' => Carbon::now()
                        ]);

                }
            });

    }

    public function processNibssOutward($tranDate, $batchNumber, $requestedBy): void
    {
        $outwardGl = env('NIBSS_OUTWARD_GL');
        DB::connection('sqlsrv_nibss')->table('NibssOutwardTranBankFbnM')
            ->whereDate('TransactionDate', $tranDate)
            ->orderBy('SessionId')->chunk(1000, function ($outwardData) use ($batchNumber, $requestedBy, $outwardGl) {
                $count = 1;
                foreach ($outwardData as $data) {
                    //Log::info(json_encode($data));
                    $sessionId = '';
                    $nameInquiryRef = '';
                    $sourceInstitution = '';
                    $sourceAccountNumber = '';
                    $sourceAccountName = '';
                    $amount = 0;
                    $paymentReference = '';
                    $beneficiaryAccountNumber = '';
                    $beneficiaryAccountName = '';
                    $narration = '';

                    $sessionId = $data->SessionId;
                    $nameInquiryRef = $data->NameInquiryRef;
                    $sourceInstitution = $data->FinancialInstitutionId;
                    $amount = $data->Amount;
                    $entryDate = substr($data->TransactionDate,0,19);
                    $beneficiaryAccountNumber = $data->BeneficiaryAccountNumber;
                    $beneficiaryAccountName = $data->BeneficiaryAccountName;
                    $sourceAccountNumber =  $data->SourceAccountNumber;
                    $sourceAccountName = $data->SourceAccountName;
                    $narration = $data->Narration;
                    Log::info("$batchNumber $sessionId $amount");
                    if(checkNibssOutward($batchNumber, $sessionId, $amount)){
                        DB::table('nibss_settlements')->where('Amount', $amount)
                            ->where('BatchNumber', $batchNumber)
                            ->where('SessionId', $sessionId)
                            ->where('Direction', 'Outward')
                            ->update([

                                'SessionIdBank' => $sessionId,
                                'AmountBank' => $amount,
                                'SourceInstitutionBank' => $sourceInstitution,
                                'DestinationAccountNameBank' => $beneficiaryAccountName,
                                'DestinationAccountNumberBank' => $beneficiaryAccountNumber,
                                'EntryDateBankBank' => $entryDate,
                                'Status' => 'Reconciled',
                                'updated_at' => Carbon::now()
                            ]);
                    }else{
                        //Todo reversed wrong credit
                        Log::info("Reversal");
                        //$narration = 'REV ' . $sessionId; //TBD
                        NibssPostingEntry::create([
                            'BatchNumber' => $batchNumber,
                            'SessionId' => $sessionId, 'Amount' => $amount,
                            'TransactionTime' => $entryDate,
                            'SourceInstitution' => $sourceInstitution,
                            'Destination' => $data->FinancialInstitutionId,
                            'DestinationAccountName' => $beneficiaryAccountName,
                            'EntryDate' => $entryDate,
                            'DestinationAccountNumber' => $beneficiaryAccountNumber,
                            'DebitAccountNumber' => $outwardGl,
                            'CreditAccountNumber' =>  $sourceAccountNumber,
                            'PostingAmount' => $amount, 'TranType' => 'Reversal',
                            'Narration' => $narration, 'RequestedBy' => $requestedBy,
                            'Status' => 'Reversal',
                            'ThreadId' => $count
                        ]);

                        $count++;
                        if($count > 10)
                            $count = 1;
                    }
                    Log::info(json_encode($data));
                }

            });

        DB::table('nibss_settlements')
            ->where('BatchNumber', $batchNumber)
            ->where('Status', 'Successful')
            ->where('Direction', 'Outward')
            ->orderBy('Id')->chunk(1000, function ($nibbsData) use ($batchNumber, $requestedBy, $outwardGl) {
                $count = 1;
                foreach ($nibbsData as $data) {
                    //Todo settlement
                    Log::info("UnImpacted");
                    NibssPostingEntry::create([
                        'BatchNumber' => $batchNumber,
                        'SessionId' => $data->SessionId, 'Amount' => $data->Amount,
                        'TransactionTime' => $data->TransactionTime,'SourceInstitution' => $data->SourceInstitution,
                        'DestinationAccountName' => $data->DestinationAccountName, 'EntryDate' => $data->TransactionTime,
                        'DestinationAccountNumber' => $data->DestinationAccountNumber,
                        'DebitAccountNumber' => $data->SourceAccountNumber,
                        'CreditAccountNumber' => $outwardGl,
                        'PostingAmount' => $data->Amount, 'TranType' => 'UnImpacted',
                        'Narration' => $data->Narration,
                        'SenderName' => $data->SenderName,
                        'RequestedBy' => $requestedBy,
                        'Status' => 'UnImpacted',
                        'ThreadId' => $count
                    ]);
                    DB::table('nibss_settlements')->where('id', $data->id)
                        ->update([
                            'Status' => 'UnImpacted',
                            'updated_at' => Carbon::now()
                        ]);
                    $count++;
                    if($count > 10)
                        $count = 1;
                }
            });

    }

}
