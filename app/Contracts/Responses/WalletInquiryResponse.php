<?php

namespace App\Contracts\Responses;

class WalletInquiryResponse extends DefaultResponse
{
    public $walletName;
    public $availableBalance;
    public $email;
    public $phoneNumber;
    public $address;
    public $ledgerBalance;
    public $walletOpenDate;

}