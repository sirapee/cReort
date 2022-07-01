<?php


namespace App\Contracts\Requests;


class FundsTransferFields
{
    public $sourceAccountNumber;
    public $originatorName;
    public $destinationAccountNumber;
    public $destinationAccountName;
    public $destinationBankCode;
    public $narration;
    public $paymentReference;
    public $amount;
    public $uniqueKey;
    public $currency;
    public $fee;
    public $valueDate;

}