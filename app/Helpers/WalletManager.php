<?php


namespace App\Helpers;
use App\WalletMaster;
use App\AccountMasterExtension;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WalletManager
{
    public function createWallet($accountDetails){

        try{

            $newAccount = $this->storeWalletInfo($accountDetails);
            Log::info('Wallet Creation Successful for '. json_encode($newAccount));
            return intval($newAccount->id);

        }catch(\Exception $e){
            Log::info('Wallet Creation failed for '. json_encode($accountDetails));
            Log::info($e->getMessage());
            return 7; //Account Creation failed
        }


    }



    /**
     * @param $accountDetails
     */
    private function storeWalletInfo($accountDetails)
    {
        Log::info(json_encode($accountDetails));
        $newAccount = WalletMaster::create([
            'bvn' => $accountDetails['bvn'],
            'wallet_name' => $accountDetails['walletName'],
            'short_name' => $accountDetails['shortName'],
            'date_of_birth' => $accountDetails['dob'],
            'email' => $accountDetails['email'],
            'wallet_ownership' => $accountDetails['walletOwnership'],
            'phone_number' => $accountDetails['phoneNumber'],
            'merchant_id' => $accountDetails['merchantId'],
            'application_id' => $accountDetails['appId'],
            'wallet_open_date' => Carbon::today(),
            'created_by' => 'SystemUser',

        ]);
        return $newAccount;
    }


}