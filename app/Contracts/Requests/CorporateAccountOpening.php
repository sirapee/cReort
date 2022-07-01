<?php


namespace App\Contracts\Requests;


use Carbon\Carbon;

class CorporateAccountOpening
{
    public $email;
    public $corporateName;
    public $keyContactPerson;
    public $address;
    public $dateOfIncorporation;
    public $primaryRmId = 'DSOY005002';
    public $schemeCode;
    public $schemeType;
    public $glSubHeadCode;
    public $limitAmount = 0;
    public $sectorCode;
    public $subSectorCode;
    public $taxId;
    public $registrationNumber;
    public $segment;
    public $subSegment;
    public $currency;
    public $solId;
    public $phoneNumber;
    public $city = '0029';
    public $state  = '15';
    public $cifId;
    public $countryOfIssue = 'NG';
    public $introducerCode = 'IU1311003';
    public $documentIssueDate;
    public $documentCode = 'PSPRT';
    public $documentTypeCode = 'RET_ID';
    public $placeOfIssue = '0075';
    public $referenceNumber;
    public $sanctionDate;
    public $expiryDate;
    public $requestType = 'Account Creation';
    public $signatories = [];
    public $appId;

}