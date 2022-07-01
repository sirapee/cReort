<?php


namespace App\Contracts\Requests;


class CardDebitOneOffFields
{
    public $customerId ;
    public $pan;
    public $nameOnCard;
    public $cvv;
    public $transactionAmount;
    public $expiryMonth;
    public $expiryYear;
    public $transactionReference;
    public $transactionFee;
    public $scheme;
    public $narration = 'Add Card Request';
    public $cardHolder;
    public $currency = 'NGN';
    public $pin;
    public $applicationId = "EWallet";

}