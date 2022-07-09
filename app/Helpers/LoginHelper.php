<?php
/**
 * Created by PhpStorm.
 * User: sn027890
 * Date: 6/28/2018
 * Time: 6:41 PM
 */

namespace App\Helpers;
use Adldap\Laravel\Facades\Adldap;
use App\LoginDetail;
use App\Session;
use Carbon\Carbon;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Sentinel;
use Uuid;
use Cache;


class LoginHelper
{
    private $logPath;

    public  function __construct()
    {
        $this->logPath = public_path(config('app.log_path'));
    }

    public function login(){
        $userId = Sentinel::getUser()->emp_id;
        $clientIp = HelperFunctions::getClientIp();
        $browserInfo = $this->getBrowserInfo();
        $sessionId = Uuid::generate()->string;
        if($this->isLoggedIn($userId))
            return 1;  //User already logged in

        if (!$this->createSession($userId,$clientIp,$sessionId))
            return 2; // / could not create session;

        if (!$this->storeLogin($userId,$clientIp,$browserInfo))
            return 3; // could not store login details;

        session(['session_id' => $sessionId]);
        return 0; //login successful
    }

    public function logout(){
        $userId = Sentinel::getUser()->emp_id;
        $this->deleteSession($userId);

        return 0; //

    }

    public function validateSession(){
        $userId = Sentinel::getUser()->emp_id;
        if (Session::where('user_id',$userId)
            ->where('session_id',session('session_id'))
            ->first())
            return true;

        return false;
    }


    private function createSession($userId,$clientIp,$sessionId){
        try {
            $session = new Session();
            $session->user_id = $userId;
            $session->client_ip = $clientIp;
            $session->session_id = $sessionId;
            $session->save();
            $message = 'Session creation for '.$userId . ' successful';
            HelperFunctions::writeToFile($this->logPath,$message);
            return true;
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
            $message = 'Session creation for '.$userId . ' failed with '. $exception->getMessage();
            HelperFunctions::writeToFile($this->logPath,$message);
            return false;
        }

    }

    private function isLoggedIn($userId){
        if (Session::where('user_id',$userId)->first())
            return true;

        $message = 'Could not retrieve user  '.$userId . ' session, user not logged in ';
        HelperFunctions::writeToFile($this->logPath,$message);
        return false;
    }

    public function deleteSession($userId){
        if (Session::where('user_id',$userId)->delete())
            return true;

        $message = 'Session deletion for '.$userId . ' failed';
        HelperFunctions::writeToFile($this->logPath,$message);
        return false;
    }

    private function getBrowserInfo(){
        $browserComponents = HelperFunctions::getClientBrowser();
        $browserComponents = implode(';',$browserComponents);
        return $browserComponents;
    }

    private function storeLogin($userId,$clientIp, $browserInfo){
        try {
            $login = new LoginDetail();
            $login->user_id = $userId;
            $login->client_ip = $clientIp;
            $login->browser_info = $browserInfo;
            $login->login_time = Carbon::now();
            $login->save();
            $message = 'Login details storage for '.$userId . ' successful';
            HelperFunctions::writeToFile($this->logPath,$message);
            return true;
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
            $message = 'Login details storage for '.$userId . ' failed with '. $exception->getMessage();
            HelperFunctions::writeToFile($this->logPath,$message);
            return false;
        }

    }

    public static function CLILogin($username, $password): bool
    {
        $loginUsername = strtolower($username);
        $adminUsers = config('app.admin_users');
        if (in_array($loginUsername, $adminUsers, true)){
            return self::AdminCLILogin($username, $password);
        }else{
            return self::ADCLILogin($username, $password);
        }
    }

    private static function AdminCLILogin($username, $password): bool
    {
        $credentials = ['username' => $username, 'password' => $password];
        try {
            if ($user= Sentinel::authenticate($credentials)){
                return true;
            }else{
                return false;
            }
        } catch (ThrottlingException $e){
            Log::info($e->getMessage());
            return false;
        }
        catch (\Exception $e){
            $message = $e->getMessage();
            Log::info($message);
            return false;
        }
    }

    private static function ADCLILogin($username, $password): bool
    {
        $domain = '@'.self::companyDomain();
        $check = strpos($username, $domain);
        if ($check !== false) {
            $username = str_replace($domain, '', $username);
        }

        try {
            if (Adldap::auth()->attempt($username, $password)) {
                return true;
            }else{
                Log::info('Invalid credentials '. $username);
                return false;
            }

        } catch (ThrottlingException $e) {
            $delay = $e->getDelay();
            Log::info('You have entered invalid credentials numerous times, you account has been suspended for '. $delay . ' for '. $username);
            return false;
        }catch (\Exception $e){
            $message = $e->getMessage();
            Log::info($message);
            return false;
        }
    }

    private static function companyDomain(){
        return config('app.domain');
    }


}

?>
