<?php


namespace App\Helpers;
use App\CustomerAddress;
use App\CustomerEmail;
use App\CustomerMaster;
use App\CustomerPhonenumber;
use Faker\Factory;
use Illuminate\Support\Facades\Log;
use DB;

class CustomerManager
{
    public function createCustomer($customerData){
        $firstName = $customerData['firstName'];
        $lastName = $customerData['lastName'];
        $gender = $customerData['gender'];
        $email = $customerData['email'];
        $salutation = $customerData['salutation'];
        $phoneNumber = $customerData['phoneNumber'];
        $address = $customerData['address'];
        $dob = $customerData['dob'];
        $middleName = '';
        if(array_key_exists('middleName', $customerData)){
            $middleName = $customerData['middleName'];
        }
        DB::beginTransaction();
        try{
            $faker = Factory::create();
            $cust = CustomerMaster::create([
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'middle_name' => $middleName,
                'email'    => $email,
                'salutation' => $salutation,
                'gender'  => $gender,
                'phone_number'    => $phoneNumber,
                'address' => $address,
                'dob'    => $dob,
                'preferred_name'    => $firstName,
                'occupation' => $faker->jobTitle,
                'passport_number' => $faker->numberBetween(1900000,10000000),
                'license_number' => $faker->numberBetween(1900000,10000000),
                'introducer_id' => $faker->numberBetween(1900000,10000000),
                'created_by' => 'AccountUser'
            ]);
            Log::info($cust->id);
            $customer = CustomerMaster::where('id',$cust->id)->first();


            CustomerEmail::create([
                'customer_id' => $customer->customer_id,
                'email'  => $customer->email,
                'end_date'    => '2099-12-31',
                'preferred_flag' => 'Y',
                'created_by' => $customer->created_by,

            ]);

            CustomerPhonenumber::create([
                'customer_id' => $customer->customer_id,
                'phone_number'  => $customer->phone_number,
                'phone_local_code'  => $faker->locale,
                'end_date'    => '2099-12-31',
                'preferred_flag' => 'Y',
                'created_by' => $customer->created_by,

            ]);

            CustomerAddress::create([
                'customer_id' => $customer->customer_id,
                'address'  => $customer->address,
                'end_date'    => '2099-12-31',
                'preferred_flag' => 'Y',
                'created_by' => $customer->created_by,

            ]);
            Log::info('Customer creation Successful for '. json_encode($customerData));
            DB::commit();
            return intval($customer->id);
        }catch (\Exception $e){
            DB::rollback();
            Log::info($e->getMessage());
            Log::info('Customer creation failed for '. json_encode($customerData));
            return 6;  //Customer creation failed
        }

    }

}