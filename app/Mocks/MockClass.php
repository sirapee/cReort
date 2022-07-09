<?php

namespace App\Mocks;

use Carbon\Carbon;

class MockClass
{
    public function postilionData(){
        return [
            [
                'Id' => 1,
                'Pan' => '539983******8776',
                'RequestDate' => Carbon::today(),
                'AccountNumber' => '227084732401005000',
                'DateLocal' => Carbon::now(),
                'ResponseCode' => '0',
                'RetrievalReferenceNumberPostilion' => '101339497',
                'Stan' => '2973',
                'TranType' => '1',
                'AmountPostilion' => 1000,
                'TerminalIdPostilion' => '10301123',
                'MessageType' => '200',
                'IssuerName' => 'GTBank',
                'TransactionType1' => '',


            ],
            [
                'Id' => 2,
                'Pan' => '418745******4592',
                'RequestDate' => Carbon::today(),
                'AccountNumber' => '801990207',
                'DateLocal' => Carbon::now(),
                'ResponseCode' => '51',
                'RetrievalReferenceNumberPostilion' => '101339508',
                'Stan' => '9449',
                'TranType' => '1',
                'AmountPostilion' => 0,
                'TerminalIdPostilion' => '10300854',
                'MessageType' => '200',
                'IssuerName' => 'Bank Card Not Listed',
                'TransactionType1' => '',


            ],
            [
                'Id' => 3,
                'Pan' => '418745******4592',
                'RequestDate' => Carbon::today(),
                'AccountNumber' => '801990207',
                'DateLocal' => Carbon::now(),
                'ResponseCode' => '51',
                'RetrievalReferenceNumberPostilion' => '101339508',
                'Stan' => '9449',
                'TranType' => '1',
                'AmountPostilion' => 0,
                'TerminalIdPostilion' => '10300854',
                'MessageType' => '200',
                'IssuerName' => 'Bank Card Not Listed',
                'TransactionType1' => '',


            ],
        ];
    }

}
