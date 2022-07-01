<?php


namespace App\Contracts\Requests;


class CardDebitFields
{
    public $cardId;
    public $customerId;
    public $transactionAmount;
    public $transactionFee;
    public $narration;
    public $applicationId = "EWallet";

}