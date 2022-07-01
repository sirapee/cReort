<?php


namespace App\Helpers;


use App\Contracts\AddCardFields;
use App\Contracts\Responses\AddCardResponse;
use Illuminate\Support\Facades\Log;

class UPPaymentGateWay
{
    public $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.api_gateway_baseurl');
    }

    public function addCard (AddCardFields $cardFields){
    //public static function addCard (){

        $url = $this->baseUrl.'/api/v1/paymentGateway/AddCard';
        $method =  'POST';
        $headers = createCurlHeaders($url, $method);
        Log::info('Headers => '. json_encode($headers));

        $body = [
            'customerId' => $cardFields->customerId,
            'pan' => $cardFields->pan,
            'nameOnCard' => $cardFields->nameOnCard,
            'cvv' => $cardFields->cvv,
            'cardExpiryDate' => $cardFields->cardExpiryDate,
            'scheme' => $cardFields->scheme,
            'narration' => $cardFields->narration,
            'cardHolder' => $cardFields->cardHolder,
            'pin' => $cardFields->pin,
            'applicationId' => $cardFields->applicationId
        ];
        Log::info('Body => '. json_encode($body));
        $jsonEncodedBody = json_encode($body);
        $addCardResponse = curlCallRestApi($url, $headers, $jsonEncodedBody, $method);
        Log::info($addCardResponse);
        $mapper = new \JsonMapper();
        $addCardObject = $mapper->map(json_decode($addCardResponse), new AddCardResponse());
        if($addCardObject->status != 'Success'){
            return null;
        }
        Log::info($addCardObject->url);
        return $addCardObject->url;
    }

    public function cardTransactionInquiry($transactionId){
        $url = $this->baseUrl.'/api/v1/paymentGateway/ConfirmAddCardStatus?transactionId='.$transactionId;
        $method =  'GET';
        $headers = createCurlHeaders($url, $method);
        Log::info($url);
        $cardTxnResponse = curlCallRestApi($url, $headers, null, $method);
        return $cardTxnResponse;

    }

}
