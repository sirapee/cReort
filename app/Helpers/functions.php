<?php


use App\ClientConfiguration;
use App\Contracts\Requests\AlertUpload;
use App\Contracts\Requests\BvnLink;
use App\Helpers\LogActivity;
use App\Helpers\States;
use App\Models\AccountCreationRequest;
use App\Models\Alert;
use App\Models\BulkAccountOpen;
use App\Models\BulkCorporateAccountUpload;
use App\Models\BulkCustomerUpdateUpload;
use App\Models\CorporateAccountCreationRequest;
use App\Models\SystemConfiguration;
use App\RunningProcess;
use App\Upload;
use App\UserManagementAudit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helpers\HelperFunctions;
use App\Helpers\FileUpload;
use App\AccountOpening;
use Illuminate\Filesystem\Filesystem;





function getSettlementPath(){
    $path =   config('app.settlement_path');
    checkCreateFolder($path);
    return $path;
}

function getReversalPath(){
    $path =   config('app.reversal_path');
    checkCreateFolder($path);
    return $path;
}

function nonce()
{
    return substr(str_shuffle(MD5(microtime())), 0, 20);
}
function Base64Encode($value)
{
    $base64value =  base64_encode($value);
    return $base64value;
}
function Base64Decode($value)
{
    $base64value =  base64_decode($value);
    return $base64value;
}
function SHA256($signaturecipher)
{
    return hash("sha256",$signaturecipher);
}

function SHA1Here($signaturecipher)
{
    return sha1($signaturecipher, false);
}

function getLoggedInUser(){
    $id = auth()->user()->id;
    $user = Sentinel::findById($id);
    $role =  Sentinel::findById($id)->roles()->first()->slug;
    $user->role = $role;
    return $user;
    //return auth()->user();
}



function getLoggedInStaffId(){
    $processor = getLoggedInUser();
    return $processor->emp_id;
}

function getTokenLifeTime(){
    return (int)config('app.token_lifetime');
}

function timestamp()
{
    return time();
}

function twoFactorUrl(){
    return  config('app.two_factor_url');
}

function twoFactorUniqueKey(){
    return   config('app.two_factor_unique_key');
}

function tokenUrl(){
    return  config('app.identity_authority');
}

function getClientId(){
    return  config('app.client_id');

}

function getClientSecret(){
    return  config('app.client_secret');

}

function defaultSchemeCode(){
    return  config('app.default_scheme_code');

}

function defaultSavingsSchemeCode(){
    return  config('app.default_savings_scheme_code');

}

function bulkMakerChecker(){
    return  config('app.bulk_maker_checker');

}

function defaultParentCif(){
    return  config('app.default_parent_cif');

}

function defaultCurrentSchemeCode(){
    return  config('app.default_current_scheme_code');

}

function defaultSchemeType(){
    return  config('app.default_scheme_type');

}

function defaultSol(){
    return  config('app.default_sol');
}


function defaultCurrency(){
    return  config('app.default_currency');
}

function defaultIntroducerCode(){
    return  config('app.default_introducer_code');
}

function defaultRelationshipManager(){
    return  config('app.default_relationship_manager');
}

function defaultSolId(){
    return  config('app.default_sol');
}

function defaultSector(){
    return  config('app.default_sector');
}

function defaultSubSector(){
    return  config('app.default_sub_sector');
}


function getAccountsLogPath(){
    $path =   config('app.accounts_path'). '\\logs\\';
    checkCreateFolder($path);
    return $path;
}

function getToken (){
    $clientId = getClientId();
    $clientSecret = getClientSecret();
    $grantType = 'client_credentials';
    return  generateIdentityToken($clientId, $clientSecret,$grantType);
}

function curlCallRestApi($url, $headers, $jsonEncodedBody, $method){
    $curl = curl_init();
    if ($jsonEncodedBody == null){
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ]);
    }else{
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $jsonEncodedBody,
            CURLOPT_HTTPHEADER => $headers,
        ]);
    }
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function createHeaders($url, $method){
    $token = getToken();
    $uniqueKey = twoFactorUniqueKey();
    $encodeUrl = urlencode($url);
    $reference = nonce();
    $clientId = getClientId();
    $clientSecret = getClientSecret();
    $baseStringToBeSigned = $method . "&" . $encodeUrl . "&" . $reference. "&". $clientId . "&" . $clientSecret;
    Log::info($baseStringToBeSigned);
    $sign = Base64Encode(SHA1Here($baseStringToBeSigned));
    $auth = 'HeritageAuth ' . Base64Encode ($clientId);

    $headers = [];
    $headers[] = 'Content-type: application/json';
    $headers[] = 'UniqueKey: '.$uniqueKey;
    $headers[] = 'Reference: '.$reference;
    $headers[] = 'Signature: '.$sign;
    $headers[] = 'HBAuthorization: '.$auth;
    $headers[] = 'Authorization: Bearer '.$token;

    return $headers;
}

function createHeadersNoToken(): array
{
    $headers[] = 'Content-type: application/json';
    return $headers;
}

function generateIdentityToken($clientId, $clientSecret, $grantType, $scope = null)
{
    try{
        $tokenUrl = config('app.identity_authority');
        if ($scope == null){
            $loginBody = [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => $grantType
            ];
        }else{
            $loginBody = [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => $scope,
                'grant_type' => $grantType
            ];
        }

        $loginResponse = callService($tokenUrl, $loginBody);
        $token = $loginResponse['access_token'];
        return $token;
    }catch (\Exception $exception){
        Log::info($exception->getMessage());
        return null;
    }
}

function callServiceHttp($url, $method, $jsonEncodedBody, $headers ){
    $client = new http\Client();
    $request = new http\Client\Request();


    $method = strtoupper($method);
    $request->setRequestUrl($url);
    $request->setRequestMethod($method);
    $body = new http\Message\Body;
    $body->append($jsonEncodedBody);
    $request->setBody($body);
    $request->setOptions(array());
    $request->setHeaders($headers);
    $client->enqueue($request)->send();
    $response = $client->getResponse();
    return $response->getBody();
}

function getUploadFilepath(){
    $path =   config('app.accounts_path'). '\\Uploads\\';
    checkCreateFolder($path);
    return $path;
}

function getMandateFilepath(){
    $path =   config('app.accounts_path'). '\\mandate\\';
    checkCreateFolder($path);
    return $path;
}

function getDocumentsFilepath(){
    $path =   config('app.accounts_path'). '\\documents\\';
    checkCreateFolder($path);
    return $path;
}

function getUserMandateFilepath(){
    $path =   config('app.accounts_path'). '\\mandate\\'.strtoupper(getLoggedInStaffId());
    checkCreateFolder($path);
    return $path;
}

function getSettlementFilepath(){
    $format = 'Ymd';
    $now = date($format );
    $path =   config('app.settlement_path'). '\\'.$now.'\\';//.strtoupper(getLoggedInStaffId());
    checkCreateFolder($path);
    return $path;
}

function getAccountsReportPath(){
    $path =   config('app.accounts_path').'\\reports\\';
    checkCreateFolder($path);
    return $path;
}

function companyName(){
    return config('app.company_name');
}

function validateToken($username, $tokenCode, $userGroup){
    $url = twoFactorUrl();
    $uniqueKey = twoFactorUniqueKey();
    $token = 'g3vjZnR4qd-CF0lpTQIT-l_pzbkRQwiYOxXAXb2jNLyjNjTCZmwvd-Zf18EDnBJZam7mpdsCTFozTfAV-ky4K--VgMmtnuWQyoD6x9kO_AAOqz2QXnx6UaSrneu6QehgB6nCJB9ApAD3D2uzLznlfaCkFtEzspAmM0QybCPGSJXWzRpFgmL0zhFigZBY5esvi4dsYMD2dM-XgSFqPGjDiFHs2ImCsJJeOtP569LV2NAE1ZaYdmPk66ni1fXSJV1QWTAkW2gYlNLALCeYXMVosnFIavo39YfFPdTcukVlWEFe5EKx__qCZU0dReYAqidYc02dVHJlL4VU0f4nU3oGs7eyDlGjUQ7w1YHHAX0CqWhNwlkRrJg7UK5qvXbGGdkIjbcmY0bk29pp5jOF1zP8o-g1h1ltRgMf9XvotCMO-G3j34FXiNzkWRn46jZpwUBKd8heYgiQMb4KVpumYbOtk-pQP14GGJin2MaA8rHQgTV5wT0bQz7URtSUZpL1cMZG';
    $headers = [
        'Authorization' => 'Bearer ' . $token,
        'Accept'        => 'application/json',
    ];
    $body = [
        'UserName' => $username,
        'TokenCode' => $tokenCode,
        'UserGroup' => $userGroup,
        'UniqueKey' => $uniqueKey
    ];
    return  callService($url, $body, $headers);
}

/**
 * @param $text
 * @param $fields
 * @param $batchNumber
 */
function accountsWriteLog($text, $batchNumber ='APINA')
{
    writeToFile(getAccountsReportPath() .'\\'. $batchNumber . '.txt', $text);//
    writeLogs($text);
    writeToFile(getAccountsLogPath() .date('dmY') . '.txt', $text);
    return true;
}

function checkCreateFolder($path){
    if (!file_exists($path)) {
        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
    }
    return true;
}

function base64EncodedImage($path){
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $encodedImage = Base64Encode($data);
    return $encodedImage;
}

function validateBase64Image($base64String){
    $str = base64_decode($base64String, true);
    writeLogs($str);
    if ($str === false ) {
        return false;
    }
    return true;
}

function moveFile($sourceFile, $destinationDirectory){
    $filesystem = new Filesystem();
    $path = $sourceFile;
    $filesystem->move($path,$destinationDirectory);
    return true;
}


function writeToFile($path,$content){
    $format = 'Y/m/d H:i:s';
    $now = date($format );
    file_put_contents(($path), $now.' '.$content."\n", FILE_APPEND | LOCK_EX);
    return true;
}

/**
 * SFTP helper
 *
 * @param $file
 * @param $fileName
 * @param $destinationPath
 * @param $name
 * @return Boolean
 */
function fileSFTP($content, $destinationPath, $fileName, $name='sftp'){
    try {
        $destinationFile = $destinationPath .'/'.$fileName;
        if (!Storage::disk($name)->exists($destinationPath)){
            if(config('app.make_ftp_dir_if_not_exist') == 'Y'){
                Storage::disk($name)->makeDirectory($destinationPath);
            }else{
                Log::info($destinationPath. ' does not exist');
                return false;
            }
        }
        Storage::disk($name)->put($destinationFile, $content);
        Log::info($fileName.' file transferred successfully!');
        return true;
    }catch(\Exception $e){
        Log::info($e->getMessage());
        return false;
    }
}



/**
 * @param $value
 * @return array
 */
function getSheetValues($value)
{

    $salutation = htmlspecialchars(str_replace("'", "", trim($value->salutation)));
    $firstname = htmlspecialchars(str_replace("'", "", trim($value->firstname)));
    $middlename = htmlspecialchars(str_replace("'", "", trim($value->middlename)));
    $lastname = htmlspecialchars(str_replace("'", "", trim($value->lastname)));
    $email = htmlspecialchars(str_replace("'", "", trim($value->email)));
    $manager = htmlspecialchars(str_replace("'", "", trim($value->manager)));
    if($manager == '' || $manager == null){
        $manager = 'RT002OF08';
    }
    $gender = htmlspecialchars(str_replace("'", "", trim($value->gender)));
    $bvn = htmlspecialchars(str_replace("'", "", trim($value->bvn)));
    $phonenumber = htmlspecialchars(str_replace("'", "", trim($value->phonenumber)));
    $cifid = htmlspecialchars(str_replace("'", "", trim($value->cifid)));
    $schemecode = htmlspecialchars(str_replace("'", "", trim($value->schemecode)));
    if($schemecode == '' || $schemecode == null){
        $schemecode = 'SBLLA';
    }
    $schemetype = htmlspecialchars(str_replace("'", "", trim($value->schemetype)));
    if($schemetype == '' || $schemetype == null){
        $schemetype = 'SBA';
    }
    $glsubheadcode = htmlspecialchars(str_replace("'", "", trim($value->glsubheadcode)));
    if ($glsubheadcode == '' || $glsubheadcode == null){
        $glsubheadcode = '29000';
    }
    //
    $currency = htmlspecialchars(str_replace("'", "", trim($value->currency)));
    if ($currency == '' || $currency == null){
        $currency = 'NGN';
    }
    $introducercode = htmlspecialchars(str_replace("'", "", trim($value->introducercode)));
    if ($introducercode == '' || $introducercode == null){
        $introducercode = 'IU1311003';
    }
    $solid = htmlspecialchars(str_replace("'", "", trim($value->solid)));
    if ($solid == '' || $solid == null){
        $solid = '001';
    }
    $city = htmlspecialchars(str_replace("'", "", trim($value->city)));
    if ($city == '' || $city == null){
        $city = '75';
    }
    $country = htmlspecialchars(str_replace("'", "", trim($value->country)));
    if ($country == '' || $country == null){
        $country = 'NG';
    }
    $postalcode = htmlspecialchars(str_replace("'", "", trim($value->postalcode)));
    if ($postalcode == '' || $postalcode == null){
        $postalcode = '234';
    }
    $state = htmlspecialchars(str_replace("'", "", trim($value->state)));
    if ($state == '' || $state == null){
        $state = '17';
    }
    $occupation = htmlspecialchars(str_replace("'", "", trim($value->occupation)));
    if ($solid == '' || $solid == null){
        $solid = 'OTH';
    }
    $birthdate = htmlspecialchars(str_replace("'", "", trim($value->birthdate)));
    $birthmonth = htmlspecialchars(str_replace("'", "", trim($value->birthmonth)));
    $birthyear = htmlspecialchars(str_replace("'", "", trim($value->birthyear)));
    $address = htmlspecialchars(str_replace("'", "", trim($value->address)));
    $iscustomernre = htmlspecialchars(str_replace("'", "", trim($value->iscustomernre)));
    $isminor = htmlspecialchars(str_replace("'", "", trim($value->isminor)));
    $staffflag = htmlspecialchars(str_replace("'", "", trim($value->staffflag)));
    $staffemployeeid = htmlspecialchars(str_replace("'", "", trim($value->staffemployeeid)));
    if ($staffflag == 'Y' && ($solid == '' || $solid == null)){
        $solid = 'SYSTEM';
    }
    $maritalstatus = htmlspecialchars(str_replace("'", "", trim($value->maritalstatus)));
    $card_request = htmlspecialchars(str_replace("'", "", trim($value->card_request)));
    $card_type = htmlspecialchars(str_replace("'", "", trim($value->card_type)));
    $enable_alert = htmlspecialchars(str_replace("'", "", trim($value->enable_alert)));
    $initial_deposit = htmlspecialchars(str_replace("'", "", trim($value->initial_deposit)));
    if ($initial_deposit == '' || $initial_deposit == null){
        $initial_deposit = '0';
    }
    $limitamount = htmlspecialchars(str_replace("'", "", trim($value->limitamount)));
    if ($limitamount == '' || $limitamount == null){
        $limitamount = '0';
    }
    $sanctiondate = htmlspecialchars(str_replace("'", "", trim($value->sanctiondate)));
    $sanctionrefno = htmlspecialchars(str_replace("'", "", trim($value->sanctionrefno)));
    $limitexpirydate = htmlspecialchars(str_replace("'", "", trim($value->limitexpirydate)));
    $repaymentaccount = htmlspecialchars(str_replace("'", "", trim($value->repaymentaccount)));
    $deposittermdays = htmlspecialchars(str_replace("'", "", trim($value->deposittermdays)));
    $deposittermmonths = htmlspecialchars(str_replace("'", "", trim($value->deposittermmonths)));
    $depositamount = htmlspecialchars(str_replace("'", "", trim($value->depositamount)));
    $interesttablecode = htmlspecialchars(str_replace("'", "", trim($value->interesttablecode)));
    $renewaltermdays = htmlspecialchars(str_replace("'", "", trim($value->renewaltermdays)));
    $renewaltermmonths = htmlspecialchars(str_replace("'", "", trim($value->renewaltermmonths)));
    $acctprefint = htmlspecialchars(str_replace("'", "", trim($value->acctprefint)));
    if ($acctprefint == '' || $acctprefint == null){
        $acctprefint = '0';
    }
    $debitacctountid = htmlspecialchars(str_replace("'", "", trim($value->debitacctountid)));

   // $valueDate = trim($value->value_date);
    //$valueDate = HelperFunctions::createDate('Y-m-d', $valueDate);

    return array($salutation , $firstname , $middlename , $lastname , $email , $manager , $gender , $bvn , $phonenumber ,
            $cifid , $schemecode , $schemetype , $glsubheadcode , $currency , $introducercode , $solid , $city , $country ,
        $postalcode , $state , $occupation , $birthdate , $birthmonth , $birthyear , $address , $iscustomernre , $isminor ,
        $staffflag , $staffemployeeid , $maritalstatus , $card_request , $card_type , $enable_alert , $initial_deposit ,
        $limitamount , $sanctiondate , $sanctionrefno , $limitexpirydate , $repaymentaccount , $deposittermdays ,
        $deposittermmonths , $depositamount , $interesttablecode , $renewaltermdays , $renewaltermmonths , $acctprefint , $debitacctountid );
}


function collectionToArray($oldValues)
{
    $oldValues = $oldValues->map(function ($oldValues) {
        return collect($oldValues->toArray())
            ->except(['deleted_at', 'created_at', 'updated_at', 'created_by', 'updated_by', 'id'])
            ->all();
    });
    return $oldValues;
}

function collectionToArrayUsers($oldValues)
{
    $oldValues = $oldValues->map(function ($oldValues) {
        return collect($oldValues->toArray())
            ->except(['deleted_at', 'created_at', 'updated_at', 'created_by',
                'verified_by', 'modified_by', 'id', 'last_login', 'deleted',  'profilePix', 'modified_date', 'permissions'])
            ->all();
    });
    return $oldValues;
}

function getDownloadLimit(){
    return (int)(config('app.download_limit'));
}

function maxUploadRowsInstant(){
    return (int)((config('app.max_upload_row_instant')));
}

function createDateFromFormat($format, $dateString, $resultFormat = null){
    if ($dateString == null || $dateString == ''){
        return null;
    }
    //dd(Carbon::createFromFormat($format, $dateString));
    if ($resultFormat == null)
        return Carbon::createFromFormat($format, $dateString);
    return  Carbon::parse(Carbon::createFromFormat($format, $dateString))->format($resultFormat);
}

function executeOracleFunction($functionName, $bindings){
    $result = DB::connection('oracle')->executeFunction($functionName, $bindings, $returnType = PDO::PARAM_STR, $length = 999);
    return $result;
}


function getClientIp()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}


function callService($url,$body, $headers = null){

    $client = new \GuzzleHttp\Client(['verify' => false]);
    if($headers == null){
        $response = $client->request("POST", $url, ['form_params'=>$body]);
    }else{
        $response = $client->request("POST", $url, ['headers' => $headers,'form_params'=>$body]);
    }
    $response = json_decode($response->getBody(), TRUE);
    Log::info($response);
    return $response;
}

function callServiceGet($url, $headers = null){

    $client = new \GuzzleHttp\Client(['verify' => false]);
    if($headers == null){
        $response = $client->request("GET", $url);
    }else{
        $response = $client->request("GET", $url, ['headers' => $headers]);
    }
    $response = json_decode($response->getBody(), TRUE);
    Log::info($response);
    return $response;
}

function storeRunningJobs($jobId,$processType,$batchNumber,$settlementType){
    $format = 'Y/m/d H:i:s';
    $now = date($format );
    $id = DB::table('running_processes')->insertGetId(
        [
            'job_id' => $jobId,
            'process_type' => $processType,
            'settlement_type' => $settlementType,
            'batch_number'=> $batchNumber,
            'created_at' => $now,
            'created_by' => Sentinel::getUser()->username
        ]
    );
    return $id;
}

function updateJobStatus(){
    $runningJobs = RunningProcess::all();
    foreach ($runningJobs as $runningJob){
        $count = DB::table('jobs')->where('id',$runningJob->job_id)
            ->count();
        if($count == 0){
            $format = 'Y/m/d H:i:s';
            $now = date($format );
            DB::table('running_processes')
                ->where('job_id', $runningJob->job_id)
                ->whereNotIn('status',['C','E','F'])
                ->update([
                    'status' => 'C',
                    'updated_at' => $now
                ]);
        }
    }
}

function jobStatus(){
    updateJobStatus();
    $runningJobs = RunningProcess::whereIn('process_type',['Reconciliation','Settlement'])
        ->orderBy('created_at','desc')->paginate(5);
    return $runningJobs;
}


function checkRunningProcess($processType,$settlementType){
    return DB::table('running_processes')
        ->where('process_type',$processType)
        ->where('settlement_type', $settlementType)
        ->where('status','R')
        ->count();
}

function writeLogs($message){
    if (Sentinel::check()){
        $user = Sentinel::getUser()->username;
    }else{
        $user = 'anonymous';
    }
    Log::info($user."\n" . $message);
    return true;
}

function randomNumber($length) {
    $result = '';

    for($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }

    return $result;
}

function  cacheClear(){
    Cache::forget('pending_verification_count');
    Cache::forget('pending_post_count');
    Cache::forget('duplicated_count');
    Cache::forget('pending_batch_details');
    Cache::forget('fi_txn_for_post');
    Cache::forget('fi_txn_summary');
    Cache::forget('fi_pending_verification_count');
    Cache::forget('settlement_txn_summary');
    Cache::forget('settlement_pending_verification_count');//pending_verification_count
    Cache::forget('pending_reconciliation_fin');
    Cache::forget('pending_reconciliation_fin_accounts');
    Cache::forget('get_set_batch_summary_details_charges');
    Cache::forget('get_set_pending_batch_details');
    Cache::forget('get_set_pending_batch_charge_details');
    Cache::forget('charges_pending_verification');
    Cache::forget('reversal_pending_verification_count');
    Cache::forget('reversal_txn_summary');
    Cache::forget('reversal_pending_posting_count');
}

function acceptableUploadFileSize($fileSize){
    if ($fileSize >= getMaxUploadFileSize()){
        return false;
    }
    return true;
}

function isMaxUploadRowInstant($rowCount){
    if($rowCount > maxUploadRowsInstant()){
        return false;
    }
    return true;
}

function getMaxUploadFileSize(){
    return (int)(config('app.max_upload_file_size'));
}

function getMaxUploadRows(){
    return (int)(config('app.max_upload_row'));
}

function isMaxAllowedRows($rowCount){
    if ($rowCount > getMaxUploadRows()){
        return false;
    }
    return true;
}

function downloadExcel($tableName,$reportName,$queryParameters,  $connection= 'sqlsrv'){
    $queryResult = getExcelData($tableName,$queryParameters, $connection);
    prepareDownloadData($queryResult,$reportName,Sentinel::getUser()->username)->export('xlsx');
}

function storeExcelData($tableName,$reportName,$queryParameters, $inputter, $path,$connection= 'sqlsrv'){
    $queryResult = getExcelData($tableName,$queryParameters, $connection);
    prepareDownloadData($queryResult,$reportName,$inputter)->store('xlsx', $path);
}

function storeAccountDownloadData($startDate,$endDate,$reportName,$inputter, $path){
    $queryResult = settlementAccountsTransactionExcelData($startDate, $endDate);
    prepareDownloadData($queryResult,$reportName,$inputter)->store('xlsx', $path);
    return true;
}

function getExcelData($tableName,$parameters, $connection) {
    $limit = getDownloadLimit();
    return DB::connection($connection)->table($tableName)
        ->where($parameters)
        ->limit($limit)
        ->get()
        ->map(function ($item, $key) {
            return (array) $item;
        })
        ->all();
}

function prepareDownloadData($queryResult,$reportName,$author) {
    return Excel::create($reportName, function($excel) use($reportName,$queryResult, $author) {
        // Set the title
        $sheetTitle = substr($reportName,0 , 30);
        $excel->setTitle($sheetTitle);
        $companyName = companyName();
        // Chain the setters
        $excel->setCreator($author)
            ->setCompany($companyName);

        // Call them separately

        $description = substr($reportName,0,20);
        $excel->setDescription($description);

        $sheetTitle = substr($reportName,0,10);
        $excel->sheet($sheetTitle, function($sheet) use($queryResult) {

            $sheet->fromArray($queryResult);
        });
    });
}

function cacheQuery($sql, $timeout = 60) {
    return Cache::remember(md5($sql), $timeout, function() use ($sql) {
        return DB::select(DB::raw($sql));
    });
    //$cache = $this->cacheQuery("SOME COMPLEX JOINS ETC..", 30);
}


/**
 * @param $accountNumber
 * @param $phoneNumber
 * @param $email
 * @param string $showBal
 * @param string $maskAcct
 * @param string $alertOption
 * @param string $statusFlag
 * @param $staffId
 */
function storeAlertDetails($accountNumber, $phoneNumber, $email, string $showBal, string $maskAcct, string $alertOption, string $statusFlag, $staffId, $batchNumber): void
{
    Alert::create([
        'account_number' => $accountNumber, 'phone_number' => $phoneNumber, 'email' => $email, 'show_balance' => $showBal,
        'mask_account_number' => $maskAcct, 'alert_option' => $alertOption, 'status_flag' => $statusFlag,'batch_number' => $batchNumber,
        'processed_by' => $staffId, 'approved_by' => $staffId
    ]);
}

/**
 * @param $accountNumber
 * @param $phoneNumber
 * @param $email
 * @param string $showBal
 * @param string $maskAcct
 * @param string $alertOption
 * @param string $statusFlag
 * @param $staffId
 */
function storeBvnDetails($accountNumber, $bvn, $customerId, $bvnDate, $corporateCustomerId, $retailsCustomerid, $solId, $staffId, $accountType, $batchNumber): void
{
    \App\Models\BvnLink::create([
        'account_number' => $accountNumber, 'bvn' => $bvn, 'customer_id' => $customerId, 'retail_customer_id' => $retailsCustomerid,
        'corporate_customer_id' => $corporateCustomerId, 'bvn_date' => $bvnDate, 'audit_sol' => $solId,'batch_number' => $batchNumber,
        'processed_by' => $staffId, 'approved_by' => $staffId, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'account_type' => $accountType
    ]);
}

/**
 * @param Request $request
 * @param array $response
 * @param $staffId
 * @return array
 */
function ProcessAlert(AlertUpload $request, $staffId): string
{
    Log::info(json_encode($request));
    $showBal = 'N';
    if (!empty($request->showBalance))
        $showBal = $request->showBalance;
    $accountNumber = $request->accountNumber;
    $accountDetails = getAccountDetails($accountNumber);
    if ($accountDetails == null) {
        return "Invalid Account Number";
    }
    $phoneNumber = $request->phoneNumber;
    $email = $request->email;
    $alertOption = "";
    $statusFlag = 'Y';
    $maskAcct = 'N';
    if (!empty($email) && !empty($phoneNumber)) {
        $alertOption = 'BOTH';
    } elseif (!empty($email) && empty($phoneNumber)) {
        $alertOption = 'EMAIL';
    } elseif (empty($email) && !empty($phoneNumber)) {
        $alertOption = 'SMS';
    }
    $acid = $accountDetails->acid;
    $ipAddress = getClientIp();
    $functionCode = 'A';
    $result = getRelatedPartiesDetails($accountNumber);
    Log::info($result);
    foreach ($result as $det) {
        $cifId = $det->nma_key_id;
        Log::info($cifId);
        Log::info(checkPhone($phoneNumber, $cifId));
        if (!checkPhone($phoneNumber, $cifId)) {
            //return "The Phone Number is not that of a Signatory on the account, check and try again";
        }

        if (!checkEmail($email, $cifId)) {
            //return "The Email is not that of a Signatory on the account, check and try again";
        }
    }
    Log::info(checkPhoneAlert($phoneNumber, $accountNumber));
    if (checkPhoneAlert($phoneNumber, $accountNumber)) {
        return "The Phone Number already maintained for this account";
    }

    if (checkEmailAlert($email, $accountNumber)) {
        return "The Email Number already maintained for this account";
    }

    //Check if alert has been setup on the account before
    if (checkAlertCount($accountNumber) >= 1) {
        $functionCode = "U";
        storeAlertOptions($accountNumber, $phoneNumber, $email);
        storeAlertAudit($accountNumber, $phoneNumber, $email, $functionCode, $staffId, $ipAddress);
    } else {
        storeAlertSetup($showBal, $accountNumber, $acid, $statusFlag, $maskAcct, $alertOption);
        storeAlertOptions($accountNumber, $phoneNumber, $email);
        storeAlertAudit($accountNumber, $phoneNumber, $email, $functionCode, $staffId, $ipAddress);
    }
    $batchNumber = $request->batchNumber;

    storeAlertDetails($accountNumber, $phoneNumber, $email, $showBal, $maskAcct, $alertOption, $statusFlag, $staffId, $batchNumber);
    return "";


}



?>