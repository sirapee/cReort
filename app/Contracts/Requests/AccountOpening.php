<?php


namespace App\Contracts\Requests;


class AccountOpening
{
    public $email;
    public $salutation;
    public $gender;
    public $address;
    public $maritalStatus;
    public $bvn;
    public $schemeCode;
    public $schemeType;
    public $glSubHeadCode;
    public $limitAmount = 0;
    public $imageFile;
    public $signatureFile;
    public $initialDeposit = 0;
    public $requestCard;
    public $profileAlert;
    public $cardType;
    public $currency;
    public $solId;
    public $phoneNumber;
    public $firstName;
    public $middleName;
    public $lastName;
    public $dob;
    public $cifId;
    public $name;
    public $introducerCode;
    public $appId;
    public $age;
    public $isMinor;
    public $parentCif;
    public $requestType = 'Account Creation';
    public $signatories = [];


}