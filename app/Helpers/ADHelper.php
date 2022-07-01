<?php
/**
 * Created by PhpStorm.
 * User: ini-obong.udoh
 * Date: 15/01/2018
 * Time: 07:09
 */

namespace app\Helpers;


use Adldap\Laravel\Facades\Adldap;
use App\User;
use Illuminate\Support\Facades\Log;
use Sentinel;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use App\soap\nusoap_client;



class ADHelper
{
    public function ADLogin($request)
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
                return $employeeid;
            }else{
                return 1;
            }

        } catch (ThrottlingException $e) {
            $delay = $e->getDelay();
            Log::info('You have entered invalid credentials numerous times, you account has been suspended for '. $delay . ' for '. $username);
            return 2;
        } catch (NotActivatedException $e) {
            $message = $e->getMessage();
            Log::info($message);
            return 3;
        }catch (\Exception $e){
            $message = $e->getMessage();
            Log::info($message);
            return 4;
        }
    }

    public function unlock($request)
    {
        if (Adldap::auth()->attempt($request->username, $request->password)) {
            return true;
        }
        return false;
    }

    public function TwoFactor ($request){

        try {
            $this->validateToken('IU1311003','staff','0019882900ww','7tnGtLgEeEIMVOt4DjlQk3od035ZCWmtLnqcywJKm+I');
            return true;
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
            return false;
        }
    }

    private function validateToken($userId,$tokenGroup,$tokenPin,$authCode){
        $client = new nusoap_client('http://10.0.33.62/Test_EntrustBridge/API.svc?singleWsdl', 'wsdl');
        $client->soap_defencoding='utf-8';
        $err = $client->getError();
        if ($err) {
            Log::info($err);
            dd($err);
        }

        $param = array('userId'=>$userId,'tokenGroup'=>$tokenGroup,'tokenPin'=>$tokenPin ,'authCode'=>$authCode);
        $result = $client->call('tokenROAuthenticate', array('parameters' => $param), '', '', false, true);
        // Check for a fault
        if ($client->fault) {
            Log::info($result);
            dd($result);
            return false;
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                Log::info($err);
                dd($err);
                return false;
            } else {
                Log::info($result);
                dd($result['tokenROAuthenticateResult']);
            }
        }
    }

    public function ADSearch($request){
        if ($search = Adldap::search()->where('employeeid', '=', $request->emp_id)->firstOrFail()) {
            return $search;
        }
        return false;
    }
}