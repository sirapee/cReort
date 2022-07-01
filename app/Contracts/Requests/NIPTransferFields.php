<?php


namespace App\Contracts\Requests;


class NIPTransferFields
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

}