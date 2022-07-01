<?php


namespace App\Contracts\Requests;


class AddCardFields
{

    public $customerId ;
    public $pan;
    public $nameOnCard;
    public $cvv;
    public $transactionAmount;
    public $cardExpiryDate;
    public $transactionReference;
    public $scheme;
    public $narration = 'Add Card Request';
    public $cardHolder;
    public $bankName;
    public $currency = 'NGN';
    public $pin;
    public $applicationId;

}