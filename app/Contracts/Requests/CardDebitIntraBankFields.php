<?php


namespace App\Contracts\Requests;


class CardDebitIntraBankFields
{
    public $cardId;
    public $customerId;
    public $transactionAmount;
    public $transactionFee;
    public $narration = "Add Card Request";
    public $transactionReference;
    public $beneficiaryAccountNo;
    public $beneficiaryName;
    public $senderName;
    public $applicationId = "EWallet";

}