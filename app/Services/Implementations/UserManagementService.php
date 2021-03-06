<?php

namespace App\Services\Implementations;

use Adldap\Laravel\Facades\Adldap;
use App\Contracts\Responses\UserManagementResponse;
use app\Helpers\HelperFunctions;
use App\Helpers\LogActivity;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserAuthorizationRequest;
use App\Http\Requests\UserCreationRequest;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserManagementAudit;
use App\Services\Interfaces\IUserManagementService;
use Carbon\Carbon;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
Use Sentinel;
use DB;
use function PHPUnit\Framework\throwException;

class UserManagementService implements IUserManagementService
{
    public UserManagementResponse $response;

    public function __construct()
    {
        $this->response = new UserManagementResponse();
    }

    public function allUser(Request $request): UserManagementResponse
    {
        //Log::info(encrypt("P@sswo0rd"));
        // Grab all the users
        $users = User::where('new_user', 'N')
            ->whereNotIn('username',['admin', 'admin2'])->get();
        // Do we want to include the deleted users?
        if ($request->get('withTrashed')) {
            $users = User::withTrashed()->get();
        } elseif ($request->get('onlyTrashed')) {
            $users = User::onlyTrashed()->get();
        }

        $this->response->isSuccessful = true;
        $this->response->data = $users;
        $this->response->responseCode = "000";
        $this->response->responseMessage = "Request Successful";
        return $this->response;
    }

    public function createNewUser(UserCreationRequest $request): UserManagementResponse
    {
        $staffId = getLoggedInStaffId();
        DB::beginTransaction();
        try {
            $roleFromUrl = $request->role;
            $systemPassword = config('app.default_key');
            $password = decrypt($systemPassword);
            $request->merge(['password' => $password]);
            $request->merge(['created_by' => $staffId]);

            $request->merge(['first_name' => $request->firstName]);
            $request->merge(['emp_id' => $request->empId]);
            $request->merge(['last_name' => $request->lastName]);
            $request->merge(['job_title' => $request->jobTitle]);
            $request->merge(['sol_id' => $request->solId]);
            $request->merge(['new_user' => 'Y']);
            $request->merge(['two_factor' => $request->twoFactor]);
            if(env("APP_ENVIRONMENT") !== "Development" && env("APP_ENVIRONMENT") !== "Test"){
                if (!$search = Adldap::search()->where('employeeid', '=', $request->empId)->firstOrFail()) {
                    //dd($search->displayname[0]);
                    $this->response->responseCode = "115";
                    $this->response->responseMessage = 'Invalid Employee Id';
                    return $this->response;
                }
                if ($search->thumbnailphoto[0] == null){
                    $request->merge(['profilePix' => 'avatar.png']);
                }else{
                    $request->merge(['profilePix' => $request->emp_id.'.png']);
                }
                if ($search->thumbnailphoto[0] != null){
                    $this->saveProfileImage($search->thumbnailphoto[0],$request->emp_id);
                }
            }

            if (User::userExists(trim($request->username))) {
                $this->response->responseMessage = 'User Already Exists';
                return $this->response;
            }
            //dd($request->all());
            if (!$user = Sentinel::register($request->except(['firstName', 'empId','lastName', 'jobTitle', 'twoFactor' ]))) {
                $this->response->responseMessage = 'User Profiling Failed';
                return $this->response;
            }
            $role = Sentinel::findRoleBySlug($roleFromUrl);
            $role->users()->attach($user);
            $this->saveProfilePix($request, $user);

            //Attach permissions
            if ($request->has('permissions')){
                foreach ($request->permissions as $permission){
                    $user->addPermission($permission,true);
                    $user->save();
                }
            }
            $subject = 'User Management Create';
            $details = $request->empId .'|' . $request->firstName .' '.$request->lastName. '|'. $request->role;
            LogActivity::addToLog($subject,$details);
            $data = [
                'function_code' => 'A',
                'user_id' => $user->id,
                'modified_field_data' => $details,
                'inputter' => $staffId,
                'created_at' => Carbon::now()
            ];
            UserManagementAudit::insert($data);

            // Redirect to the home page with success menu
            $users = User::where('new_user', 'N')->get();
            $this->response->isSuccessful = true;
            $this->response->data = $users;
            $this->response->responseCode = "000";
            $this->response->responseMessage = "User Creation Successful";
            DB::commit();
            return $this->response;
        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            Log::info($message);
            $this->response->responseMessage = $message;
            return $this->response;
        }

    }

    public function userDetails($id): UserManagementResponse
    {
        try{
            $user = User::findOrFail($id);
            // Redirect to user list if updating user wasn't existed
            $sentinelUser = Sentinel::findById($id);
            //dd(array_keys($sentinelUser->permissions));
            $userRole =  $sentinelUser->roles()->first();
            $roles = Sentinel::getRoleRepository()->get();
            $dataArray = [
                'user' => $user,
                'roles' => $roles,
                'userRole' => $userRole
            ];
            $data = (object)$dataArray;

            $this->response->isSuccessful = true;
            //$this->response->data->user = $user;
            //$this->response->data->userRole = $userRole;
            $this->response->data = $data;
            $this->response->responseCode = "000";
            $this->response->responseMessage = "Request Successful";
            return $this->response;
        }catch (\Exception $e){
            $message = $e->getMessage();
            Log::info($message);
            $this->response->responseCode = "907";
            $this->response->responseMessage = $message;
            return $this->response;
        }
    }

    public function authorizationUserDetails($id, $functionCode = 'A'): UserManagementResponse
    {
        try{
            if($functionCode ==='A'){
                $user = User::findOrFail($id);
            }
            if($functionCode ==='U'){
                $user = DB::table('users_mod')->where('user_id', $id);
            }
            if($functionCode ==='D'){
                $user = User::findOrFail($id);
                $user->deleted_at = Carbon::now();
            }
            if($functionCode ==='R'){
                $user = User::findOrFail($id);
                $user->deleted_at = Carbon::now();
            }

            // Redirect to user list if updating user wasn't existed
            $sentinelUser = Sentinel::findById($id);
            //dd(array_keys($sentinelUser->permissions));
            $userRole =  $sentinelUser->roles()->first();
            $dataArray = [
                'user' => $user,
                'userRole' => $userRole
            ];
            $data = (object)$dataArray;

            $this->response->isSuccessful = true;
            //$this->response->data->user = $user;
            //$this->response->data->userRole = $userRole;
            $this->response->data = $data;
            $this->response->responseCode = "000";
            $this->response->responseMessage = "Request Successful";
            return $this->response;
        }catch (\Exception $e){
            $message = $e->getMessage();
            Log::info($message);
            $this->response->responseCode = "907";
            $this->response->responseMessage = $message;
            return $this->response;
        }
    }

    public function getDetails($id): UserManagementResponse
    {
        try {
            if(env("APP_ENVIRONMENT") !== "Development" && env("APP_ENVIRONMENT") !== "Test"){
                if ($search = Adldap::search()->where('employeeid', '=', $id)->firstOrFail()) {
                    $names = explode(' ', $search->displayname[0]);
                    if ($names[1] == '') {
                        $names[1] = $names[2];
                    }
                    $details = [
                        'department' => $search->department[0],
                        'username' => strtolower($search->samaccountname[0]),
                        'email' => strtolower($search->mail[0]),
                        'first_name' => $names[0],
                        'last_name' => $names[1],
                        'job_title' => $search->title[0]
                    ];
                    $this->response->isSuccessful = true;
                    $this->response->data = $details;
                    $this->response->responseCode = "000";
                    $this->response->responseMessage = "User Creation Successful";
                    return $this->response;
                }
                $this->response->responseMessage = "Could not Get User Details";
                return $this->response;
            }



            $faker = Factory::create();
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $username = strtolower($firstName .'.'.$lastName);
            $email = $username.'@.tys.com';
            $department = $faker->randomElement(['System Access Control', 'E-Banking Control', 'Regional Control', 'Business Services']);
            $jobTitle = $department . ' Officer';
            $details = [
                'department' => $department,
                'username' => $username,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'job_title' => $jobTitle
            ];
            $this->response->isSuccessful = true;
            $this->response->data = $details;
            $this->response->responseCode = "000";
            $this->response->responseMessage = "User Creation Successful";
            return $this->response;

        } catch (\Exception $e) {
            Log::info($e->getMessage());
            $this->response->responseCode = "907";
            $this->response->responseMessage = $e->getMessage();
            return $this->response;
        }/**/

    }

    public function updateUser(UpdateUserRequest $request, $id = null): UserManagementResponse
    {

        $staffId = getLoggedInStaffId();

        try{
            $user = User::findOrFail($id);
            $auditDetails = UserManagementAudit::where('user_id', $id)
                ->whereNull('authorizer')->first();
            Log::info($auditDetails);
            if ($auditDetails !== '')
            {
                $this->response->responseCode = "119";
                $this->response->responseMessage = ' Verification Pending for ' . $user->first_name .' !!!';
                return $this->response;
            }
            $request->merge(['first_name' => $request->firstName]);
            $request->merge(['emp_id' => $request->empId]);
            $request->merge(['last_name' => $request->lastName]);
            $request->merge(['job_title' => $request->jobTitle]);
            $request->merge(['two_factor' => $request->twoFactor]);
            $request->merge(['user_id' => $id]);
            $request->merge(['sol_id' => $request->solId]);
            $request->merge(['sol_id' => $request->solId]);
            $request->merge(['user_id' => $id]);
            $request->merge(['role' => $request->role]);
            $data = $request->except(['firstName', 'empId','lastName', 'jobTitle', 'twoFactor', 'permissions', 'solId']);
            $this->response->data = $data;
           //return $this->response;
            DB::table('users_mod')->insert($data);

            $data = [
                'function_code' => 'U',
                'user_id' => $id,
                'modified_field_data' => json_encode($request->all()),
                'inputter' => $staffId,
                'created_at' => Carbon::now()
            ];
            UserManagementAudit::insert($data);
            $subject = 'User Management Update';
            $details = $request->emp_id .'|' . $request->first_name .' '.$request->last_name. '|'. $request->role;
            LogActivity::addToLog($subject,$details);
            $users = User::where('new_user', 'N')->get();
            $this->response->isSuccessful = true;
            $this->response->data = $users;
            $this->response->responseCode = "000";
            $this->response->responseMessage = "Request Successful";
            return $this->response;
        }catch (\Exception $e){
            $message = $e->getMessage();
            $this->response->responseCode = "907";
            $this->response->responseMessage = $message;
            return $this->response;
        }/**/

    }

    public function getRoles(): UserManagementResponse{
        $roles = Sentinel::getRoleRepository()->get();
        $this->response->isSuccessful = true;
        $this->response->data = $roles;
        $this->response->responseCode = "000";
        $this->response->responseMessage = "Request Successful";
        return $this->response;
    }

    public function authorizeUser(UserAuthorizationRequest $request): UserManagementResponse
    {

        $processor = getLoggedInStaffId();
        DB::beginTransaction();
        try{
            $approveOrReject =  $request->approveOrReject;
            $userId = trim($request->userId);
            $auditDetails = UserManagementAudit::where('user_id', $userId)
                ->whereNull('authorizer')->first();
            if(empty($auditDetails)){
                $this->response->responseCode = "119";
                $this->response->responseMessage = "Nothing Pending for this User";
                return $this->response;
            }
            $auditId = $auditDetails->id;

            //maker checker
            if(HelperFunctions::makerChecker($auditId,$processor, 'UserManagementAudit')){
                $this->response->responseCode = "119";
                $this->response->responseMessage = "Inputter and Authorizer cannot be the same";
                return $this->response;
            }
            $functionCode =  $auditDetails->function_code;

            $sentinelUser = $this->activateUser($userId);
            $users = User::All();

            $this->approveOrReject($userId, $functionCode, $auditId, $approveOrReject);

            $subject = 'User Management Verification';
            $details = $sentinelUser->emp_id .'|' . $sentinelUser->first_name .' '.$sentinelUser->last_name. '|'. $sentinelUser->role;
            LogActivity::addToLog($subject,$details);
            $this->response->isSuccessful = true;
            $this->response->data = $users;
            $this->response->responseCode = "000";
            $this->response->responseMessage = "User Management Verification Successful";

            DB::commit();

            return $this->response;


        }catch (\Exception $e){
             DB::rollback();
             Log::info($e->getMessage());
             $this->response->responseCode = "907";
             $this->response->responseMessage = $e->getMessage();
             return $this->response;
         }/* */

    }

    public function pendingAuthorization(): UserManagementResponse
    {
        $pendingDetails = HelperFunctions::getPendingUsersByBatch();
        $this->response->isSuccessful = true;
        $this->response->data = $pendingDetails;
        $this->response->responseCode = "000";
        $this->response->responseMessage = "Request Successful";
        return $this->response;
    }

    public function permissionsDetails($slug) : UserManagementResponse
    {
        try{
            $permissions = DB::table('permissions')->where('slug',$slug)->orderBy('name', 'asc')->get();
            $this->response->isSuccessful = true;
            $this->response->data = $permissions;
            $this->response->responseCode = "000";
            $this->response->responseMessage = "Request Successful";
            return $this->response;
        }catch (\Exception $e){
            Log::error($e->getMessage());
            $this->response->responseCode = "907";
            $this->response->responseMessage = $e->getMessage();
            return $this->response;
        }
    }

    public function deleteUser($userId): UserManagementResponse
    {

        $staffId = getLoggedInStaffId();
        try{
            $user = User::findOrFail($userId);

            if (UserManagementAudit::where('user_id', $userId)
                ->whereNull('authorizer')->exists())
            {
                $this->response->responseCode = "119";
                $this->response->responseMessage = ' Verification Pending for ' . $user->first_name .' !!!';
                return $this->response;
            }

            UserManagementAudit::insert([
                'function_code' => 'D',
                'user_id' => $userId,
                'modified_field_data' => 'Delete User',
                'inputter' => $staffId,
                'created_at' => Carbon::now()
            ]);
            $users = User::All();
            $subject = 'User Management Delete';
            $details = $user->emp_id .'|' . $user->first_name .' '.$user->last_name. '|'. $user->role;
            LogActivity::addToLog($subject,$details);
            $this->response->isSuccessful = true;
            $this->response->data = $users;
            $this->response->responseCode = "000";
            $this->response->responseMessage = "User Deletion Initiated Successful";
            return $this->response;
        }catch (\Exception $e){
            $message = $e->getMessage();
            Log::info($message);
            $this->response->responseCode = "907";
            $this->response->responseMessage = $message;
            return $this->response;
        }

    }

    public function restoreUser($userId): UserManagementResponse
    {

        $staffId = getLoggedInStaffId();
        try{
            $user = User::withTrashed()->findOrFail($userId);

            if (UserManagementAudit::where('user_id', $userId)
                ->whereNull('authorizer')->exists())
            {
                $this->response->responseCode = "119";
                $this->response->responseMessage = ' Verification Pending for ' . $user->first_name .' !!!';
                return $this->response;
            }

            UserManagementAudit::insert([
                'function_code' => 'R',
                'user_id' => $userId,
                'modified_field_data' => 'Restore User',
                'inputter' => $staffId,
                'created_at' => Carbon::now()
            ]);
            $users = User::All();
            $subject = 'User Management Delete';
            $details = $user->emp_id .'|' . $user->first_name .' '.$user->last_name. '|'. $user->role;
            LogActivity::addToLog($subject,$details);
            $this->response->isSuccessful = true;
            $this->response->data = $users;
            $this->response->responseCode = "000";
            $this->response->responseMessage = "User Restoration Initiated Successful";
            return $this->response;
        }catch (\Exception $e){
            $message = $e->getMessage();
            Log::info($message);
            $this->response->responseCode = "907";
            $this->response->responseMessage = $message;
            return $this->response;
        }

    }


    private function saveProfileImage($image,$staffId){
        $data = $image; // replace with an image string in bytes
        $file = imagecreatefromstring($data); // php function to create image from string
        $filename = 'uploads/'.$staffId;

        if ($file !== false)
        {
            // saves an image to specific location
            $resp = imagepng($file, $filename.'.png');
            // frees image from memory
            imagedestroy($file);
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param Request $request
     * @param $user
     */
    private function saveProfilePix(Request $request, $user)
    {
        $extension = 'png';
        $upload = new Upload();
        $upload->mime = 'img';
        $upload->original_filename = '';
        $upload->filename = $request->emp_id . '.' . $extension;
        $user->uploads()->save($upload);
    }

    private function activateUser(string $userId){
        $sentinelUser = Sentinel::findById($userId);

        if (!Activation::completed($sentinelUser))
        {
            $activation = Activation::create($sentinelUser);
            Activation::complete($sentinelUser,  $activation->code);
        }
        return $sentinelUser;
    }

    private function approveOrReject($userId, $functionCode, $auditId, $approveOrReject): void
    {
        $processor = getLoggedInStaffId();
        if(trim($functionCode) === "A"){
            //$user = DB::table('users')->where('id', $userId)->first();
            if($approveOrReject === "A"){
                //$this->activateUser($userId);
                DB::table('users')
                    ->where('id', $userId)
                    ->update(['new_user' => 'N']);
            }else{
                DB::table('users')
                    ->where('id', $userId)
                    ->delete();
            }

        }
        elseif(trim($functionCode) === "D"){
            //$user = DB::table('users')->where('id', $userId)->first();
            if($approveOrReject === "A"){
                User::where('id',$userId)->delete();
            }
        }
        elseif(trim($functionCode) === "R"){
            //$user = DB::table('users')->where('id', $userId)->first();
            if($approveOrReject === "A"){
                User::withTrashed()->where('id',$userId)->restore();
            }
        }
        else{
            $userMod = DB::table('users_mod')->where('user_id', $userId)->first();
            if(empty($userMod))
                throw new \Exception("No Modification Pending for this user");
            if($approveOrReject === "A"){
                DB::table('users')
                    ->where('id', $userMod->user_id)
                    ->update(['new_user' => 'N',
                        'email'       => $userMod->email,
                        'username'       => $userMod->username,
                        'password'    => "cherub",
                        'first_name'  => $userMod->first_name,
                        'last_name'   => $userMod->last_name,
                        'emp_id' => $userMod->emp_id,
                        'two_factor' => $userMod->two_factor,
                        'sol_id' => $userMod->sol_id,
                        'region' => $userMod->region

                        ]);
                $sentinelUser = Sentinel::findById($userId);
                $role = Sentinel::findRoleBySlug($userMod->role);
                $userRoles =  $sentinelUser->roles()->get();
                //$userRole->users()->detach($sentinelUser);

                foreach ($userRoles as $userRole){
                    $userRole->users()->detach($sentinelUser);
                }
                $role->users()->attach($sentinelUser);
                //Attach permissions
                /*if ($request->has('permissions')){
                    foreach ($request->permissions as $permission){
                        $sentinelUser->addPermission($permission,true);
                        $sentinelUser->save();
                    }
                }*/
                DB::table('users_mod')
                    ->where('user_id', $userId)
                    ->delete();
            }else{
                DB::table('users_mod')
                    ->where('user_id', $userId)
                    ->delete();
            }
        }
        DB::table('user_management_audit')
            ->where('id', $auditId)
            ->update(['authorizer' => $processor, 'approved_or_rejected' => $approveOrReject, 'updated_at' => Carbon::now()]);

    }

    /**
     * @return false|string
     */
    private function nowdate()
    {
        $format = 'Y/m/d H:i:s';
        $now = date($format);
        return $now;
    }
}
