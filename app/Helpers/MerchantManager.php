<?php


namespace App\Helpers;
use App\Merchant;
use App\AccountMasterExtension;
use App\Models\ApplicationAudit;
use App\Models\MerchantMod;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;

class MerchantManager
{
    public function createMerchant($accountDetails){

        try{

            $newAccount = $this->storeMerchantInfo($accountDetails);
            Log::info('Merchant Creation Successful for '. json_encode($newAccount));
            return intval($newAccount->id);

        }catch(\Exception $e){
            Log::info('Wallet Creation failed for '. json_encode($accountDetails));
            Log::info($e->getMessage());
            return 7; //Account Creation failed
        }


    }

    public function updateMerchant($request){

        try{
            $data= [
                'merchant_name' => $request->merchantName,
                'email' => $request->email,
                'address' => $request->address,
                'state' => $request->state,
                'notification_url' => $request->notificationUrl,
                'phone_number' => $request->phoneNumber,
                'updated_at' => Carbon::today(),

            ];
            $user = getLoggedInUser();
            DB::table('merchants')->where('merchant_id', $request->merchantId)
                ->update($data);
            ApplicationAudit::create([
                'user_id' => $user->id,
                'function_code' => 'U',
                'modified_field_data' => json_encode($data),
                'inputter' => $user->emp_id,
                'authorizer' => $user->emp_id,
                'approved_or_rejected' => 'A',

            ]);
            Log::info('Merchant Update Successful for '. json_encode($request));
            return true;

        }catch(\Exception $e){
            Log::info('Wallet Creation failed for '. json_encode($request));
            Log::info($e->getMessage());
            return false; //Merchant Update failed
        }


    }

    public function updateMerchants($request){
        $user = getLoggedInUser();
        try{
            $data= [
                'merchant_name' => $request->merchantName,
                'old_id' => $request->id,
                'merchant_id' => $request->merchantId,
                'email' => $request->email,
                'address' => $request->address,
                'state' => $request->state,
                'notification_url' => $request->notificationUrl,
                'core_account_number' => $request->coreAccountNumber,
                'phone_number' => $request->phoneNumber,
                'created_by' => $user->emp_id,
                'updated_at' => Carbon::today(),
            ];

            $merchant = MerchantMod::create($data);
            ApplicationAudit::create([
                'table_id' => $merchant->id,
                'function_code' => 'U',
                'modified_field_data' => json_encode($data),
                'inputter' => $user->emp_id,

            ]);
            Log::info('Merchant Update Successful for '. json_encode($request));
            return true;
        }catch(\Exception $e){
            Log::info('Merchant Creation failed for '. json_encode($request));
            Log::info($e->getMessage());
            return false; //Merchant Update failed
        }

    }

    /**
     * @param $accountDetails
     */
    private function storeMerchantInfo($accountDetails)
    {
        $user = getLoggedInUser();
        $newAccount = Merchant::create([
            'bvn' => $accountDetails['bvn'],
            'merchant_name' => $accountDetails['merchantName'],
            'state' => $accountDetails['shortName'],
            'date_of_birth' => $accountDetails['dob'],
            'address' => $accountDetails['address'],
            'core_account_number' => $accountDetails['coreAccountNumber'],
            'email' => $accountDetails['email'],
            'phone_number' => $accountDetails['phoneNumber'],
            'merchant_id' => $accountDetails['merchantId'],
            'application_id' => $accountDetails['appId'],
            'terminal_id' => $accountDetails['terminalId'],
            'notification_url' => $accountDetails['notificationUrl'],
            'created_by' => $user->emp_id,

        ]);
        ApplicationAudit::create([
            'table_id' => $newAccount->id,
            'function_code' => 'A',
            'modified_field_data' => json_encode($accountDetails),
            'inputter' => $user->emp_id,

        ]);
        return $newAccount;
    }


}