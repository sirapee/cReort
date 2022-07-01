<?php

namespace App\Contracts\Responses;

class DefaultResponse
{
    public $responseCode = "119";
    public $responseMessage;
    public $errorResponse;
    public $isSuccessful = false;

}