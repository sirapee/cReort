<?php


namespace App\Contracts\Requests;


class AuthorizeAddCardFields
{
    public $cardId;
    public $otp;
    public $transactionReference;
    public $applicationId;
}