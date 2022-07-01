<?php

namespace App\Http\Controllers\API;

use App\Services\Interfaces\ISessionService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
class SessionController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    private ISessionService $session;
    public function __construct(ISessionService $sessionService)
    {
        $this->middleware('auth:api', ['except' => ['userLogin', 'twoFactor']]);
        $this->session = $sessionService;
    }

    public function userLogin(Request $request){
        return $this->session->userLogin($request);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthenticatedUser(): \Illuminate\Http\JsonResponse
    {
        return response()->json(getLoggedInUser());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthenticatedUserWallet(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->session->getAuthenticatedUserWallet());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->session->logout());
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(): \Illuminate\Http\JsonResponse
    {
        return $this->session->refreshToken();
    }


    public function twoFactor (Request $request){
        return $this->session->twoFactor($request);
    }

}
