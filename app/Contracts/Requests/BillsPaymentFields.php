<?php


namespace App\Contracts\Requests;


class BillsPaymentFields
{
    public $accountNumber;
    public $customerId;
    public $paymentCode;
    public $uniqueKey;
    public $isRecharge =  'Y';
    public $customerMobile;
    public $customerEmail;
    public $amount;
    public $narration;
    public $fee;
    public $requestReference;
    public $applicationId = 'EWallet';

}