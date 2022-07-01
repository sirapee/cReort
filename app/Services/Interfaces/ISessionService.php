<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;

interface ISessionService
{
    public function userLogin(Request $request);

    public function getAuthenticatedUserWallet();

    public function twoFactor (Request $request);

    public function refreshToken();

    public function logout();

}
