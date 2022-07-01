<?php
/**
 * Created by PhpStorm.
 * User: Ini-Obong.Udoh
 * Date: 27/10/2017
 * Time: 18:32
 */

namespace app\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;

class TransactionTypes
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public  function balanceInquiry(){
        $this->request = $this->commonFields();
        return $this->request;
    }
    public  function accountInquiry(){
        $this->request = $this->commonFields();
        $this->request->merge(['creditAccount' => '0000000000']);
        $this->request->merge(['narration' => 'Account Inquiry']);


        return $this->request;
    }

    public  function miniStatementInquiry(){
        $this->request->merge(['creditAccount' => '0000000000']);
        $this->request->merge(['narration' => 'Mini Statement Inquiry']);
        $this->request = $this->commonFields();



        return $this->request;
    }

    public  function chequeInquiry(){
        $this->request = $this->commonFields();
        $this->request->merge(['creditAccount' => '0000000000']);
        $this->request->merge(['narration' => 'Cheque Inquiry']);


        return $this->request;
    }

    public  function cashWithdrawal(){
        $this->request = $this->commonFields();
        $this->request->merge(['creditAccount' => '0000000000']);


        return $this->request;
    }

    public  function cashDeposit(){
        $this->request = $this->commonFields();
        $this->request->merge(['debitAccount' => '0000000000']);


        return $this->request;
    }

    public  function purchase(){
        $this->request = $this->commonFields();
        return $this->request;
    }
    public  function transfer(){
        //dd($this->request);
        $this->request = $this->commonFields();
        return $this->request;
    }

    public  function debitRequest(){
        $this->request = $this->commonFields();
        return $this->request;
    }

    public  function creditRequest(){
        $this->request = $this->commonFields();
        return $this->request;
    }

    public  function preAuthorization(){
        $this->request = $this->commonFields();
        if(!$this->request->has('col125')){
            $blockId = substr($this->request->blockId,0,20);
            $blockId = str_pad($blockId,25,' ',STR_PAD_RIGHT);
            $lienReason =  '  030';
            $lienExpiryDate = $this->createDate($this->request->lienExpiryDate);
            $col125 = 'P' . $blockId. $lienReason . $lienExpiryDate . $this->request->lienRemarks;
            $this->request->merge(['col125' => $col125]);
        }
        //dd($this->request);
        return $this->request;
    }


    public  function completion(){
        $this->request = $this->commonFields();
        return $this->request;
    }

    private function generateStan(){
        return  str_random(12);
    }

    private function getDate ($format){
        //$format = 'd/m/Y H:i:s';
        $now = date($format );
        return $now;
    }

    public function commonFields()
    {
        $valueDate = $this->getDate('Y-m-d H:i:s');
        $valDate = $this->getDate('Y-m-d');
        $stan = $this->generateStan();
        if (!$this->request->has('valueDate') or $this->request->valueDate == ''){
            $this->request->merge(['valueDate' => $valueDate]);
        }
        $terminalId = str_pad($this->request->terminalId, 16, '0', STR_PAD_LEFT);
        if (!$this->request->has('refNo') or $this->request->refNo == ''){
            $this->request->merge(['refNo' => $stan]);
        }
        $this->request->merge(['terminalId' => $terminalId]);
        $this->request->merge(['valDate' => $valDate]);
        return $this->request;
    }

    private function createDate($date)
    {
        $date = date_create($date);
        $lienExpiryDate = date_format($date, "Ymd");
        return $lienExpiryDate;
    }


}