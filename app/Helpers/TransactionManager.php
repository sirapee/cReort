<?php


namespace App\Helpers;


use App\AccountMaster;
use App\TransactionMaster;
use App\TransactionMasterHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use DB;

class TransactionManager
{
    public function prepareTransactionElements($transactionElements){
        $partTran = 2;
        $fee =  0;
        $fee2 = 0;
        $fee3 = 0;
        $amount  = $transactionElements['amount'];
        $debitAccount = '';
        $creditAccount = '';
        $transactionRemarks = '';
        $transactionRemarks2 = '';
        $narration2 = '';
        $tranType = 'T';
        $tranSubType = 'CI';
        $valueDate = Carbon::today();
        if(array_key_exists('debitAccount', $transactionElements)){
            $debitAccount = $transactionElements['debitAccount'];
        }
        if(array_key_exists('creditAccount', $transactionElements) && $transactionElements['creditAccount'] != ''){
            $creditAccount = $transactionElements['creditAccount'];
        }
        if(array_key_exists('narration2', $transactionElements) && $transactionElements['narration2'] != ''){
            $narration2 = $transactionElements['narration2'];
        }
        if(array_key_exists('transaction_remarks', $transactionElements) && $transactionElements['transaction_remarks'] != ''){
            $transactionRemarks = $transactionElements['transaction_remarks'];
        }
        if(array_key_exists('transaction_remarks2', $transactionElements) && $transactionElements['transaction_remarks2'] != ''){
            $transactionRemarks2 = $transactionElements['transaction_remarks2'];
        }
        if(array_key_exists('tranType', $transactionElements)){
            $tranType = $transactionElements['tranType'];
        }
        if(array_key_exists('tranSubType', $transactionElements)){
            $tranSubType = $transactionElements['tranSubType'];
        }
        if(array_key_exists('valueDate', $transactionElements) && $transactionElements['valueDate'] != ''){
            $valueDate = $transactionElements['valueDate'];
        }
        $accountDetails = null;
        $accountNumber = null;
        $narration = $transactionElements['narration'];
        if(array_key_exists('fee', $transactionElements) && $transactionElements['fee'] != ''){
            $fee = $transactionElements['fee'];
        }
        if(array_key_exists('fee2', $transactionElements)){
            $fee2 = $transactionElements['fee2'];
        }
        if(array_key_exists('fee3', $transactionElements)){
            $fee3 = $transactionElements['fee3'];
        }



        $transactionReference = $transactionElements['transactionReference'];
        $currency = $transactionElements['currency'];
        if($fee !=  0 && $fee2 !=  0 && $fee3 !=  0){
            $partTran =  8;
        }elseif ($fee !=  0 && $fee2 !=  0){
            $partTran =  6;
        }elseif ($fee !=  0){
            $partTran =  4;
        }

        $totalAmount = $amount + $fee + $fee2 + $fee3;
        $debitDetails = getAccountDetails($debitAccount);
        if($debitDetails == null){
            Log::info($debitAccount . ' Account is invalid');
            return -1;
        }
        $creditDetails = getAccountDetails($creditAccount);
        if($creditDetails == null){
            Log::info($creditAccount . ' Account is invalid');
            return -1;
        }

        $availableBalance = getAvailableBalance($debitDetails->account_number);
        if ($debitDetails->account_ownership != 'O'){
            if ($totalAmount > $availableBalance){
                Log::info($debitAccount . ' Insufficient Balance');
                return -2;

            }

            if ($debitDetails->freeze_code  == 'D' || $debitDetails->freeze_code  == 'T'){
                Log::info($debitAccount . ' is frozen');
                return -3;

            }

            if ($creditDetails->freeze_code  == 'C' || $creditDetails->freeze_code  == 'T'){
                Log::info($creditAccount . ' is frozen');
                return -3;

            }
        }

        if ($debitDetails->currency  !=  $currency){
            Log::info($debitAccount . ' cross currency transaction not allowed');
            return -4;

        }
        if ($creditDetails->currency  != $currency){
            Log::info($creditAccount . ' cross currency transaction not allowed');
            return -4;
        }
        $solId = $debitDetails->sol_id;
        $genId = randomNumber(8);
        $tranId = 'S'.$genId;
        $tranDate = Carbon::today();
        $entryDate = Carbon::now();
        $num = intval($partTran);
        DB::beginTransaction();
        try{
            for ($i=1; $i<=$num; $i++){
                if ($i == 1){
                    $accountNumber = $debitAccount;
                    $tranAmount = $amount;
                    $partTran =  'D';
                    $accountId = $debitDetails->id;
                }elseif ($i == 2){
                    $accountNumber = $creditAccount;
                    $tranAmount = $amount;
                    $partTran =  'C';
                    $accountId = $creditDetails->id;
                }elseif ($i == 3){
                    $accountNumber = $debitAccount;
                    $tranAmount = $fee;
                    $partTran =  'D';
                    $accountId = $debitDetails->id;
                }elseif ($i == 4){
                    $accountNumber = $creditAccount;
                    $tranAmount = $fee;
                    $partTran =  'C';
                    $accountId = $creditDetails->id;
                }elseif ($i == 5){
                    $accountNumber = $debitAccount;
                    $tranAmount = $fee;
                    $partTran =  'D';
                    $accountId = $debitDetails->id;
                }elseif ($i == 6){
                    $accountNumber = $creditAccount;
                    $tranAmount = $amount;
                    $partTran =  'C';
                    $accountId = $creditDetails->id;
                }elseif ($i == 7){
                    $accountNumber = $debitAccount;
                    $tranAmount = $fee;
                    $partTran =  'D';
                    $accountId = $debitDetails->id;
                }elseif ($i == 8){
                    $accountNumber = $creditAccount;
                    $tranAmount = $fee;
                    $partTran =  'C';
                    $accountId = $creditDetails->id;
                }
                $tranEntry = $this->buildTransactionDetails($accountId, $tranId, $tranDate, $valueDate, $tranAmount, $i, $partTran, $tranType, $tranSubType, $currency, $narration, $narration2, $entryDate, $solId, $transactionReference, $transactionRemarks, $transactionRemarks2);

                //dd($tranEntry);
                if($partTran == 'D'){
                    $updateAmount = $tranAmount * -1;
                }else{
                    $updateAmount = $tranAmount;
                }
                $this->updateBalance($accountNumber, $updateAmount);
                $this->storeTransaction($tranEntry);
            }
            $numOfTran = $num/2;
            $tranHeaderEntry = $this->buildTranHeaderDetails($tranId, $tranDate, $tranType, $tranSubType, $numOfTran, $transactionRemarks);
            $this->storeTransactionHeader($tranHeaderEntry);
            DB::commit();
            Log::info('Id '. $genId. ' and Tran Id'.$tranId . ' Transaction Successful for '. json_encode($transactionElements));
            return $genId; //Transaction successful
        }catch (\Exception $e){
            Log::info($tranId . ' Transaction failed for '. json_encode($transactionElements));
            Log::info($e->getMessage());
            DB::rollback();
            return -5; //Transaction processing failed
        }

    }

    private function updateBalance($accountNumber, $amount){
        $details = getAccountDetails($accountNumber);
        $balance = doubleval($details->clear_bal_amount);
        $newBalance = $balance + $amount;
       // dd($newBalance);
        AccountMaster::where('id', $details->id)
            ->update(['clear_bal_amount' => $newBalance,
                'last_transaction_date' => Carbon::today(),
                'last_any_transaction_date'  => Carbon::today()
            ]);
    }

    private function storeTransaction($details){
        return TransactionMaster::create($details);
    }
    private function storeTransactionHeader($details){
        return TransactionMasterHeader::create($details);
    }

    /**
     * @param $accountId
     * @param string $tranId
     * @param Carbon $tranDate
     * @param $valueDate
     * @param int $tranAmount
     * @param int $i
     * @param $partTran
     * @param $tranType
     * @param $tranSubType
     * @param $currency
     * @param $narration
     * @param string $narration2
     * @param Carbon $entryDate
     * @param $solId
     * @param $transactionReference
     * @param string $transactionRemarks
     * @param string $transactionRemarks2
     * @return array
     */
    private function buildTransactionDetails($accountId, string $tranId, Carbon $tranDate, $valueDate, int $tranAmount, int $i, $partTran, $tranType, $tranSubType, $currency, $narration, string $narration2, Carbon $entryDate, $solId, $transactionReference, string $transactionRemarks, string $transactionRemarks2): array
    {
        $tranEntry = [
            'account_id' => $accountId,
            'transaction_id' => $tranId,
            'transaction_date' => $tranDate,
            'value_date' => $valueDate,
            'transaction_amount' => $tranAmount,
            'part_tran_serial_number' => $i,
            'part_transaction_type' => $partTran,
            'transaction_type' => $tranType,
            'transaction_sub_type' => $tranSubType,
            'currency' => $currency,
            'transaction_currency_code' => $currency,
            'narration' => $narration,
            'narration2' => $narration2,
            'entry_user_id' => 'Channels_user',
            'entry_date' => $entryDate,
            'gl_sub_head_code' => '123211',
            'initiation_sol_id' => $solId,
            'sol_id' => $solId,
            'transaction_reference' => $transactionReference,
            'transaction_remarks' => $transactionRemarks,
            'transaction_remarks2' => $transactionRemarks2,
            'created_by' => 'Channels_user',
            'reference_amount' => $tranAmount,
            'reference_currency_code' => $currency,
            'posted_user_id' => 'Channels_user',
            'posted_date' => Carbon::now(),
            'posted_flag' => 'Y',
            'updated_by' => 'Channels_user',
            'updated_at' => Carbon::now(),
        ];
        return $tranEntry;
    }

    /**
     * @param string $tranId
     * @param Carbon $tranDate
     * @param $tranType
     * @param $tranSubType
     * @param $numOfTran
     * @param string $transactionRemarks
     * @return array
     */
    private function buildTranHeaderDetails(string $tranId, Carbon $tranDate, $tranType, $tranSubType, $numOfTran, string $transactionRemarks): array
    {
        $tranHeaderEntry = [
            'transaction_id' => $tranId,
            'transaction_date' => $tranDate,
            'transaction_type' => $tranType,
            'transaction_sub_type' => $tranSubType,
            'number_of_debits_entered' => $numOfTran,
            'number_of_credits_entered' => $numOfTran,
            'number_of_debits_posted' => $numOfTran,
            'number_of_credits_posted' => $numOfTran,
            'transactions_remarks' => $transactionRemarks,
            'created_by' => 'Channels_user',
            'updated_by' => 'Channels_user',
            'updated_at' => Carbon::now(),
        ];
        return $tranHeaderEntry;
    }

}