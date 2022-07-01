<?php

namespace App\Services\Implementations;

use Adldap\Laravel\Facades\Adldap;
use App\Models\User;
use App\Services\Interfaces\ISessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
Use Sentinel;

class SessionService implements ISessionService
{

    public function userLogin(Request $request): \Illuminate\Http\JsonResponse
    {

        $inputs = $request->all();
        $errors = [];
        $data = [];
        $message = "";
        $status = false;
        $requiresTwoFactor = false;
        $validator = Validator::make($inputs,[
            'username' => 'required',
            'password' => 'required',
        ]);
        $code = "119";
        if (!$validator) {
            $errors = $validator->errors();
            $message = "Login Failed";
            return $this->sendResult($message,$data,$errors,$status, $code);
        }
        Log::info("Checking if the user ".$request->username." exists ");
        $loginUsername = strtolower($request->username);
        $userCheck = User::byUsername($loginUsername);
        $userCheck = empty($userCheck) ? User::byEmail($loginUsername) : $userCheck;
        $user = null;
        if(!empty($userCheck)){
            Log::info("User ".$request->username." exists ");
            Log::info(json_encode($userCheck));
            if($loginUsername === 'admin' || $loginUsername === 'administrator' || $loginUsername === 'admin2'){
                $user =  $this->adminLogin($request);
            }
            else{
                $user = $this->ADLogin($request);
                $systemPassword = config('app.default_key');
                $password = decrypt($systemPassword);
                $request->merge(['password' => $password]);
            }
        }else{
            Log::info("User ".$request->username." does not exist");
            $errors = $loginUsername. " Username not found";
            $code = "404";
            $message = "Login Failed";
            return $this->sendResult($message,$data,$errors, false, $code);
        }

        if ($user !== null){
            //$status = true;
            if ($user->two_factor === 'B') {
                $requiresTwoFactor = true;
            }
            if($user->password_expired === 'Y'){
                $errors = "The User needs to change the default password";
                $code = "101";
            }
            [$status, $errors, $message, $data] = $this->getToken($request, $requiresTwoFactor, $errors);
        }else{
            $errors = "Login failed for user ".  $loginUsername;
        }
        if($status) {
            $code = "000";
        }
        return $this->sendResult($message,$data,$errors,$status, $code);
    }


    public function getAuthenticatedUserWallet()
    {
        $user = getLoggedInUser();
        if($user->user_type == "M"){
            $merchant = getMerchantDetails($user->emp_id);
            $walletDetails = getWalletDetails($merchant->payable_wallet_number);
        }else{
            $empId = getLoggedInStaffId();
            $walletDetails = getWalletDetails($empId);
        }
        return $walletDetails;
    }

    public function twoFactor (Request $request): \Illuminate\Http\JsonResponse
    {
        $errors = [];
        $data = [];
        $message = "";
        //$status = true;

        $inputs = $request->all();

        $validator = Validator::make($inputs,[
            'username' => 'required',
            'token' => 'required',
            'password' => 'required',
        ]);

        if (!$validator) {
            //$status = false;
            $errors = $validator->errors();
            $message = "Login Failed";
            return $this->sendResult($message,$data,$errors, false);
        }
        $token = $request->token;
        $username = $request->username;
        try {
            $tokenResponse = validateToken($username,$token,'');
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
            $message = "Processing Error, Contact Support";
            return $this->sendResult($message,$data,$errors,false);
        }
        $responseCode = $tokenResponse['responseCode'];

        if($responseCode != "00"){
            $message = "Invalid Token";
            return $this->sendResult($message,$data,$errors,false);
        }

        $systemPassword = config('app.default_key');
        $password = decrypt($systemPassword);
        $user = User::byUsername($username);
        if(trim($user->user_type) === 'E'){
            $request->merge(['password' => $password]);
        }


        [$status, $errors, $message, $data] = $this->getToken($request, false, $errors);
        if ($status){
            $message = "Token Validated Successfully";
        }
        return $this->sendResult($message,$data,$errors,$status);
    }

    public function refreshToken(): \Illuminate\Http\JsonResponse
    {
        $errors = [];
        $status = true;
        $token = auth()->refresh();
        $message = "Token Refreshed Successfully";
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * getTokenLifeTime()
        ];
        return $this->sendResult($message,$data,$errors,$status);
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        $response = [
            "logoutSuccessful" => false,
            "responseMessage" => "Logout Failed"
        ];
        if (Sentinel::logout()){
            auth()->logout();
            $response['logoutSuccessful'] = true;
            $response['responseMessage'] = "Logout Successful";
            return response()->json($response);
        }
        return response()->json($response, 400);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function ADLogin(Request $request)
    {
        $check = strpos($request->username, '@hbng.com');
        if ($check !== false) {
            $username = str_replace('@hbng.com', '', $request->username);
            $request->merge(['username' => $username]);
        }
        $username = $request->input('username');
        $password = $request->input('password');
        try {
            if (Adldap::auth()->attempt($username, $password)) {
                $search = Adldap::search()->where('samaccountname', '=', $username)->firstOrFail();
                $employeeid = $search->employeeid[0];

                if ($user = User::byEmpId(trim($employeeid))) {
                    if ( $user->verified_by == '' || $user->verified_by == null)
                    {
                        Log::info('Verification Pending for this User '. $username);
                        return null;
                    }
                    //if (Sentinel::authenticate($request->all())) {
                    $sentinelUser = Sentinel::findById($user->id);

                    if($this->validateAndLogin($sentinelUser,$request)){
                        return $user;
                    }
                }else{
                    Log::info('username not found '. $username);
                    $response['responseMessage'] = 'username not found '. $username;
                    return null;
                }
            }else{
                Log::info('Invalid credentials '. $username);
                return null;
            }

        }catch (\Exception $e){
            $message = $e->getMessage();
            Log::info($message);
            return null;
        }
    }

    private function adminLogin(Request $request)
    {
        try {
            $user = Sentinel::authenticate([
                'username'    => $request->username,
                'password' => $request->password
            ]);

            $user = (empty($user)) ? Sentinel::authenticate([ 'email'  => $request->username,'password' => $request->password]) : $user;
            Log::info(" User Object" . json_encode($user));
            if (!$user){
                Log::info('Invalid Credentials');
                return null;
            }
            if($user->two_factor === 'B'){
                $response['requireTwoFactor'] = true;
            }
            $slug = Sentinel::getUser()->roles()->first()->slug;
            if($slug == 'admin' || $slug == 'admin2' || $slug == 'merchant' || $slug == 'user'){
                return $user;
            }else{
                Log::info('Invalid Role');
                return null;
            }
        }catch (\Exception $e){
            $message = $e->getMessage();
            Log::info($message);
            return null;
        }
    }
    /**
     * @param $sentinelUser
     * @return false
     */
    private function validateAndLogin($sentinelUser,$request)
    {
        if (Sentinel::login($sentinelUser)) {
            $slug = Sentinel::getUser()->roles()->first()->slug;
            $roles = ['authorizer','support','audit','sac_authorizer','sac','settlement_inputter','settlement_authorizer','sac_authorizer','ecards_inputter','ecards_authorizer'];
            ///if ($slug == 'support' || $slug == 'audit' || $slug == 'sac_authorizer' || $slug == 'sac' || $slug == 'settlement_inputter' || $slug == 'settlement_authorizer') {
            if(in_array($slug,$roles)){
                return true;
            } else {
                return false;
            }
        }else {
            return false;
        }
    }


    protected function sendResult($message,$data,$errors = [],$status = true, $code = "000")
    {
        $errorCode = $status ? 200 : 422;
        $result = [
            "message" => $message,
            "code" => $code,
            "loginSuccessful" => $status,
            "data" => $data,
            "errors" => $errors
        ];
        return response()->json($result,$errorCode);
    }

    /**
     * @param Request $request
     * @param \Illuminate\Http\JsonResponse $user
     * @param bool $requireTwoFactor
     * @return array
     */
    private function getToken(Request $request,  $requiresTwoFactor, $errors): array
    {
        $data = [];
        $credentials = $request->only("username", "password");
        $status = false;
        if (!$token = auth('api')->attempt($credentials)) {
            $errors = [
                "login" => "Invalid username or password",
            ];
            $message = "Login Failed";
        } else {
            $message = "Login Successful";
            $status = true;

            if ($requiresTwoFactor) {
                Log::info('Two Factor Required for '. $request->username );
                $data = [
                    'access_token' => null,
                    'token_type' => null,
                    'requires_two_factor' => $requiresTwoFactor,
                    'expires_in' => null
                ];
            }else{
                $data = [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'requires_two_factor' => $requiresTwoFactor,
                    'expires_in' => auth('api')->factory()->getTTL() * getTokenLifeTime()
                ];
                Log::info('Token generated for '. $request->username );
            }
        }
        return array($status, $errors, $message, $data);
    }

}
