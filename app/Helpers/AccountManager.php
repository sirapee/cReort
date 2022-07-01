<?php


namespace App\Helpers;
use App\AccountMaster;
use App\AccountMasterExtension;
use App\Models\BulkAccountUpload;
use Faker\Factory;
use Illuminate\Support\Facades\Log;

class AccountManager
{
    public function createAccount($accountDetails){
        $faker = Factory::create();
        $solId = $faker->randomElement(['001', '006', '999', '005']);
        $glSunHead = $faker->randomElement(['29001', '35011', '290012', '10024']);
        $schemeCode = $faker->randomElement(['SBLLA', 'SBSSS']);
        $schemeType = 'SBA';
        $currency = 'NGN';
        $appId = '';
        $customerId = $accountDetails['customerId'];
        $accountOwnership =$faker->randomElement(['C', 'E']);
        if(array_key_exists('appId', $accountDetails)){
            $appId = $accountDetails['appId'];
        }
        if(array_key_exists('solId', $accountDetails)){
            $solId = $accountDetails['solId'];
        }
        if(array_key_exists('glSunHead', $accountDetails)){
            $glSunHead = $accountDetails['glSunHead'];
        }
        if(array_key_exists('schemeType', $accountDetails)){
            $schemeType = $accountDetails['schemeType'];
        }
        if(array_key_exists('schemeCode', $accountDetails)){
            $schemeCode = $accountDetails['schemeCode'];
        }
        if(array_key_exists('currency', $accountDetails)){
            $currency = $accountDetails['currency'];
        }
        if(array_key_exists('accountOwnership', $accountDetails)){
            $accountOwnership = $accountDetails['accountOwnership'];
        }
        $accountNumber = substr(str_pad($faker->bankAccountNumber,10,'0', STR_PAD_RIGHT), 0,10);
        if ($schemeType == 'SBA'){
            $accountNumber = substr(str_pad('303'.$faker->bankAccountNumber,10,'0', STR_PAD_RIGHT), 0,10);
            if($accountOwnership == 'E'){
                $accountNumber = substr(str_pad('130'.$faker->bankAccountNumber,10,'0', STR_PAD_RIGHT), 0,10);
            }
        }elseif ($schemeType == 'ODA'){
            $accountNumber = substr(str_pad('501'.$faker->bankAccountNumber,10,'0', STR_PAD_RIGHT), 0,10);
            if($accountOwnership == 'E'){
                $accountNumber = substr(str_pad('530'.$faker->bankAccountNumber,10,'0', STR_PAD_RIGHT), 0,10);
            }
        }elseif ($schemeType == 'CAA'){
            $accountNumber = substr(str_pad('200'.$faker->bankAccountNumber,10,'0', STR_PAD_RIGHT), 0,10);
            if($accountOwnership == 'E'){
                $accountNumber = substr(str_pad('230'.$faker->bankAccountNumber,10,'0', STR_PAD_RIGHT), 0,10);
            }
        }elseif ($schemeType == 'OAB'){
            $accountNumber = substr(str_pad($solId.$faker->bankAccountNumber,13,'0', STR_PAD_RIGHT), 0,13);
            $accountOwnership = 'O';
        }
        try{
            $newAccount = $this->storeAccountInfo($accountDetails, $solId, $accountNumber, $glSunHead, $schemeCode, $schemeType, $currency, $accountOwnership, $faker);
            AccountMasterExtension::create([
                'account_id' => $newAccount->id,
                'free_text_2' => $appId,
                'created_by' => $accountDetails['createdBy'],
            ]);
            Log::info('Account Creation Successful for '. json_encode($newAccount));
            return intval($newAccount->id);

        }catch(\Exception $e){
            Log::info('Account Creation failed for '. json_encode($accountDetails));
            Log::info($e->getMessage());
            return 7; //Account Creation failed
        }


    }

    private function validateUploadFile($file, $batchNumber){
        $fileSize = number_format($file->getClientsize() / 1048576, 2);
        if (!acceptableUploadFileSize($fileSize)){
            $message =  'Max upload file size allowed '. getMaxUploadFileSize(). ' MB exceeded, uploaded size is ' . $fileSize .'MB' ;
            settlementWriteLog($message, $batchNumber);
            return '1|'.$message;
        }
        $uploads = new FileUpload($file);
        $filename = $file->getClientOriginalName();

        if (!$uploads::checkFile($filename)) {
            $error = 'This file ' . $filename . ' has already been uploaded!!!. If  it\'s a valid file, rename and re-upload';
            settlementWriteLog($error, $batchNumber);
            //return '2|' . $error;
        } else {
            if (!$uploads->uploadFile($this->uploadPath)) {
                $error = 'saving File failed!!!';
                settlementWriteLog($error, $batchNumber);
                return '3|' . $error;
            }
        }
        return '0|No Issues';
    }

    /**
     * @param $accountDetails
     * @param $solId
     * @param string $accountNumber
     * @param $glSunHead
     * @param $schemeCode
     * @param string $schemeType
     * @param string $currency
     * @param string $accountOwnership
     * @param \Faker\Generator $faker
     * @return mixed
     */
    private function storeAccountInfo($accountDetails, $solId, string $accountNumber, $glSunHead, $schemeCode, string $schemeType, string $currency, string $accountOwnership, \Faker\Generator $faker)
    {
        $newAccount = AccountMaster::create([
            'customer_id' => $accountDetails['customerId'],
            'sol_id' => $solId,
            'account_number' => $accountNumber,
            'account_name' => $accountDetails['accountName'],
            'account_short_name' => $accountDetails['accountShortName'],
            'gl_sub_head_code' => $glSunHead,
            'scheme_code' => $schemeCode,
            'scheme_type' => $schemeType,
            'currency' => $currency,
            'account_ownership' => $accountOwnership,
            'last_transaction_date' => \Carbon\Carbon::today(),
            'last_any_transaction_date' => \Carbon\Carbon::today(),
            'account_open_date' => $faker->date($format = 'Y-m-d', $max = 'now'),
            'created_by' => $accountDetails['createdBy'],

        ]);
        return $newAccount;
    }




}