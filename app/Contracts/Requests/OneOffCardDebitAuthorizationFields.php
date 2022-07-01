<?php


namespace App\Contracts\Requests;


class OneOffCardDebitAuthorizationFields
{
    public $oneOffInitiationId;
    public $transactionReference;
    public $otp;
    public $applicationId = "EWallet";
}