<?php
namespace App\Helpers;

use App\Models\BulkAccountUpload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;



class AccountImport implements ToModel, WithHeadingRow
{

    public function model(array $row)
    {
        newGetSheetValues($row);
        list($salutation , $firstname , $middlename , $lastname , $email , $manager , $gender , $bvn , $phonenumber ,
            $cifid , $schemecode , $schemetype , $glsubheadcode , $currency , $introducercode , $solid , $city , $country ,
            $postalcode , $state , $occupation , $birthdate , $birthmonth , $birthyear , $address , $iscustomernre , $isminor ,
            $staffflag , $staffemployeeid , $maritalstatus , $card_request , $card_type , $enable_alert , $initial_deposit ,
            $limitamount , $sanctiondate , $sanctionrefno , $limitexpirydate , $repaymentaccount , $deposittermdays ,
            $deposittermmonths , $depositamount , $interesttablecode , $renewaltermdays , $renewaltermmonths , $acctprefint , $debitacctountid) = newGetSheetValues($row);
        if ($schemetype == 'TDA'){
            if (($deposittermdays == '' || $deposittermdays == null) && ($deposittermmonths > 0)){
                $deposittermdays =  $deposittermmonths * 30;
            }
            if($deposittermmonths != 0){
                $deposittermmonths = 0;
            }
            if ($renewaltermmonths == '' || $renewaltermmonths == null){
                $renewaltermmonths =  $deposittermmonths;
            }

            if ($renewaltermdays == '' || $renewaltermdays == null){
                $renewaltermdays =  $deposittermdays;
            }
        }elseif($schemetype == 'ODA'){
            if ($sanctiondate == '' || $sanctiondate == null){
                $sanctiondate =  Carbon::today();
            }
            if ($sanctionrefno == '' || $sanctionrefno == null){
                $sanctionrefno =  generatePAN();
            }
            if ($limitexpirydate == '' || $limitexpirydate == null){
                $limitexpirydate = Carbon::today()->addDays(3);
            }
            if ($limitamount == '' || $limitamount == null){
                $limitamount = 0;
            }
        }

        if ($cifid != '' && $cifid != null){
            $custDet = GetCustomerInfoCustomerId($cifid);
            $salutation  = $custDet->cust_title_code;
            $firstname  = $custDet->cust_first_name;
            $middlename = $custDet->cust_middle_name;
            $lastname = $custDet->cust_last_name;
            $email =  $custDet->email;
            $gender  = $custDet->cust_sex;
            $dob = $custDet->date_of_birth;
            $phonenumber = 'NA';
            $address = 'NA';
            $maritalstatus = 'NA';

            $dob = HelperFunctions::createDate('Y-m-d', $dob);
            $birthdate = Carbon::parse($dob)->format('d');
            $birthmonth = Carbon::parse($dob)->format('m');
            $birthyear = Carbon::parse($dob)->format('Y');
        }
        return new BulkAccountUpload(['batch_number' => 'Test',
            'salutation' => $salutation, 'firstname' => $firstname,	'middlename' => $middlename,'lastname' => $lastname,
            'email' => $email,'manager' => $manager,'gender' => $gender, 'bvn' => $bvn, 'phonenumber' => $phonenumber,
            'cifid' => $cifid, 	'schemecode' => $schemecode,'schemetype' => $schemetype,'glsubheadcode' => $glsubheadcode,
            'currency' => $currency,'introducercode' => $introducercode,'solid' => $solid,	'city' => $city,
            'country' => $country,'postalcode' => $postalcode, 'state' => $state,'occupation' => $occupation,
            'birthdate' => $birthdate,'birthmonth' => $birthmonth,'birthyear' => $birthyear,'address' => $address,
            'iscustomernre' => $iscustomernre, 'isminor' => $isminor, 'staffflag' => $staffflag, 'staffemployeeid' => $staffemployeeid,
            'maritalstatus' => $maritalstatus, 'card_request' => $card_request, 'card_type' => $card_type,'enable_alert' => $enable_alert,
            'initial_deposit' => $initial_deposit,'limitamount' => $limitamount,'sanctiondate' => $sanctiondate,'sanctionrefno' => $sanctionrefno,
            'limitexpirydate' => $limitexpirydate,'repaymentaccount' => $repaymentaccount, 'deposittermdays' => $deposittermdays,
            'deposittermmonths' => $deposittermmonths, 'depositamount' => $depositamount,'interesttablecode' => $interesttablecode,
            'renewaltermdays' => $renewaltermdays, 'renewaltermmonths' => $renewaltermmonths, 'acctprefint' => $acctprefint,
            'debitacctountid' => $debitacctountid, 'ThreadId' => 1
        ]);
    }
}