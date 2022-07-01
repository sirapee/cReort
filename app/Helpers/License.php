<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 06/12/2017
 * Time: 10:56
 */

namespace app\Helpers;


use App\User;

class License
{
    public $secreteCode;
    public $username;
    public $password;
    public $uniqueCode;


    public function detectHardDrive(){
        if(strtoupper(PHP_OS) == strtoupper("LINUX"))
        {
            $serial = $this->linuxVolume();
        }
        else
        {
            $serial = str_replace("(","",str_replace(")","",$this->GetVolumeLabel("c")));
        }
        return $serial;
    }

    public function validateLicenceKey(){
        $secret_code = str_replace('base64:','',config('app.key'));
        if (!$licenseDetails = \App\License::first()){
            return false;
        }
        // dd($licenseDetails);
        $serial = $this->detectHardDrive();
        $license = md5($licenseDetails->username.decrypt($licenseDetails->password).trim($serial).$secret_code);
        $lisfile = $license.'.key';
        if(!file_exists($lisfile))
        {
            return false;
        }
        return true;
    }


    private function  GetVolumeLabel($drive){
        if (preg_match('#Volume Serial Number is (.*)\n#i', shell_exec('dir '.$drive.':'), $m)){
            $volname = ' ('.$m[1].')';
        }else{
            $volname = '';
        }
        return $volname;
    }

    private function linuxVolume(){
        $ds=shell_exec('udevadm info --query=all --name=/dev/sda | grep ID_SERIAL_SHORT');
        $serialx = explode("=", $ds);
        return $serialx[1];
    }

    public function generateLicenceKey($request){
        $secret_key = $request->secrete;
        $name= trim($request->name);
        $password = trim($request->password);
        $serial = trim($request->serial);
        //Todo check database to see if username and password is correct
        $licensefilename = md5($name.$password.$serial.$secret_key);
        $license =trim($request->license);
        $expiryDate = trim($request->expiry_date);
        if ($request->license_type === 'U'){
            $maxLicenseCount = config('app.unlimited_user_license');

        }elseif($request->license_type === 'L'){
            $maxLicenseCount = config('app.limited_user_license');
        }else{
            $maxLicenseCount =  10;
            $expiryDate =  date('Y-m-d', strtotime("+30 days"));
        }


        $response = [
            'message' => 'success',
            'licensefilename'=> $licensefilename,
            'licence' =>$license,
            'license_type' => $request->license_type,
            'user_license' => $maxLicenseCount,
            'licenseexpirydate' => $expiryDate
        ];

        return response()->json($response,201);

    }

    public function validateLicenceExpiry(){
        $licenseDetails = $this->licenseDetails();
        $today =  $now = date('Y-m-d' );
        if($licenseDetails->license_expiry_date < $today)
            return true;

        return false;

    }

    public function validateUserLicence(){
        $licenseDetails = $this->licenseDetails();

        //check number of users for limited licence
        if($licenseDetails->license_type === 'L'){
            $maxLicenseCount = config('app.limited_user_license');
            if ($maxLicenseCount !== $licenseDetails->user_license)
                return 1; // mismatch between DB and config setup

            $activeUsers = $this->activeUsers();
            if ($activeUsers >= $maxLicenseCount)
                return 2; //max license limit reached

        }elseif ($licenseDetails->license_type === 'T'){
            $activeUsers = $this->activeUsers();
            if ($activeUsers >= 10)
                return 2; //max license limit reached

        }
    }

    private function activeUsers(){
        $adminUsers = config('app.admin_users');
        return User::whereNotIn('username',$adminUsers)
            ->where('disabled','N')->count();
    }

    /**
     * @return mixed
     */
    private function licenseDetails()
    {
        $licenseDetails = \App\License::where('license_status', 'A')->first();
        return $licenseDetails;
    }
}
?>