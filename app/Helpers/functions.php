<?php



use App\Models\ReconRequest;
use App\Models\SolRegion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
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
function Base64Encode($value): string
{
    return base64_encode($value);
}
function Base64Decode($value): bool|string
{
    return base64_decode($value);
}
function SHA256($signaturecipher): bool|string
{
    return hash("sha256",$signaturecipher);
}

function SHA1Here($signatureCipher): string
{
    return sha1($signatureCipher, false);
}

function getLoggedInUser(){
    $id = auth()->user()->id;
    $user = Sentinel::findById($id);
    $role =  Sentinel::findById($id)->roles()->first()->slug;
    $user->role = $role;
    return $user;
    //return auth()->user();
}

function getSolRegion(): array
{
    $region = '';
    $solId = '';
    $loggedIdUser = getLoggedInUser();
    if($loggedIdUser->role === 'rco'){
        $region = $loggedIdUser->region;
    }
    if($loggedIdUser->role === 'branch.user' || $loggedIdUser->role === 'user' ){
        $solId = $loggedIdUser->sol_id;
    }

    return [$region, $solId];
}


function getLoggedInStaffId(){
    return getLoggedInUser()->emp_id;
}

function getTokenLifeTime(): int
{
    return (int)config('app.token_lifetime');
}

function timestamp(): int
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


function getAccountsLogPath(): string
{
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

function curlCallRestApi($url, $headers, $jsonEncodedBody, $method): bool|string
{
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

function createHeaders($url, $method): array
{
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
        if ($scope === null){
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
        return $loginResponse['access_token'];
    }catch (\Exception $exception){
        Log::info($exception->getMessage());
        return null;
    }
}

function callServiceHttp($url, $method, $jsonEncodedBody, $headers ): \http\Message\Body
{
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
    return $client->getResponse()->getBody();
}

function getUploadFilepath(): string
{
    $path =   config('app.accounts_path'). '\\Uploads\\';
    checkCreateFolder($path);
    return $path;
}

function getMandateFilepath(): string
{
    $path =   config('app.accounts_path'). '\\mandate\\';
    checkCreateFolder($path);
    return $path;
}

function getDocumentsFilepath(): string
{
    $path =   config('app.accounts_path'). '\\documents\\';
    checkCreateFolder($path);
    return $path;
}

function getUserMandateFilepath(): string
{
    $path =   config('app.accounts_path'). '\\mandate\\'.strtoupper(getLoggedInStaffId());
    checkCreateFolder($path);
    return $path;
}

function getSettlementFilepath(): string
{
    $format = 'Ymd';
    $now = date($format);
    $path =   config('app.settlement_path'). '\\'.$now.'\\';//.strtoupper(getLoggedInStaffId());
    checkCreateFolder($path);
    return $path;
}

function getNibbsSettlementFilepath($date): string
{
    $path =   config('app.nibss_settlement_path'). '\\'.$date.'\\';//.strtoupper(getLoggedInStaffId());
    checkCreateFolder($path);
    return $path;
}

function getAccountsReportPath(): string
{
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
function cReportWriteLog($text, $batchNumber ='APINA'): bool
{
    writeToFile(getAccountsReportPath() .'\\'. $batchNumber . '.txt', $text);//
    writeLogs($text);
    writeToFile(getAccountsLogPath() .date('dmY') . '.txt', $text);
    return true;
}

function checkCreateFolder($path): bool
{
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
    return Base64Encode($data);
}

function validateBase64Image($base64String): bool
{
    $str = base64_decode($base64String, true);
    writeLogs($str);
    if ($str === false ) {
        return false;
    }
    return true;
}

function moveFile($sourceFile, $destinationDirectory): bool
{
    $filesystem = new Filesystem();
    $path = $sourceFile;
    $filesystem->move($path,$destinationDirectory);
    return true;
}


function writeToFile($path,$content): bool
{
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
function fileSFTP($content, $destinationPath, $fileName, $name='sftp'): bool
{
    try {
        $destinationFile = $destinationPath .'/'.$fileName;
        if (!Storage::disk($name)->exists($destinationPath)){
            if(config('app.make_ftp_dir_if_not_exist') === 'Y'){
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



function collectionToArray($oldValues)
{
    return $oldValues->map(function ($oldValues) {
        return collect($oldValues->toArray())
            ->except(['deleted_at', 'created_at', 'updated_at', 'created_by', 'updated_by', 'id'])
            ->all();
    });
}

function collectionToArrayUsers($oldValues)
{
    return $oldValues->map(function ($oldValues) {
        return collect($oldValues->toArray())
            ->except(['deleted_at', 'created_at', 'updated_at', 'created_by',
                'verified_by', 'modified_by', 'id', 'last_login', 'deleted',  'profilePix', 'modified_date', 'permissions'])
            ->all();
    });
}

function getDownloadLimit(): int
{
    return (int)(config('app.download_limit'));
}

function maxUploadRowsInstant(): int
{
    return (int)((config('app.max_upload_row_instant')));
}

function createDateFromFormat($format, $dateString, $resultFormat = null): bool|Carbon|string|null
{
    if (empty($dateString)){
        return null;
    }
    //dd(Carbon::createFromFormat($format, $dateString));
    if (empty($resultFormat)) {
        return Carbon::createFromFormat($format, $dateString);
    }
    return  Carbon::parse(Carbon::createFromFormat($format, $dateString))->format($resultFormat);
}

function executeOracleFunction($functionName, $bindings){
    return DB::connection('oracle')->executeFunction($functionName, $bindings, $returnType = PDO::PARAM_STR, $length = 999);
}


function getClientIp(): bool|array|string
{

    if (getenv('HTTP_CLIENT_IP')) {
        return getenv('HTTP_CLIENT_IP');
    }

    if(getenv('HTTP_X_FORWARDED_FOR')) {
        return getenv('HTTP_X_FORWARDED_FOR');
    }

    if(getenv('HTTP_X_FORWARDED')) {
        return getenv('HTTP_X_FORWARDED');
    }
    if(getenv('HTTP_FORWARDED_FOR')) {
        return getenv('HTTP_FORWARDED_FOR');
    }
    if(getenv('HTTP_FORWARDED')) {
        return getenv('HTTP_FORWARDED');
    }
    if(getenv('REMOTE_ADDR')) {
        return getenv('REMOTE_ADDR');
    }
    return 'UNKNOWN';
}


function callService($url,$body, $headers = null){

    $client = new \GuzzleHttp\Client(['verify' => false]);
    if($headers == null){
        $response = $client->request("POST", $url, ['form_params'=>$body]);
    }else{
        $response = $client->request("POST", $url, ['headers' => $headers,'form_params'=>$body]);
    }
    $response = json_decode($response->getBody(), TRUE, 512, JSON_THROW_ON_ERROR);
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

function writeLogs($message): bool
{
    if (Sentinel::check()){
        $user = Sentinel::getUser()->username;
    }else{
        $user = 'anonymous';
    }
    Log::info($user."\n" . $message);
    return true;
}

/**
 * @throws Exception
 */
function randomNumber($length): string
{
    $result = '';

    for($i = 0; $i < $length; $i++) {
        $result .= random_int(0, 9);
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

function acceptableUploadFileSize($fileSize): bool
{
    if ($fileSize >= getMaxUploadFileSize()){
        return false;
    }
    return true;
}

function isMaxUploadRowInstant($rowCount): bool
{
    return $rowCount <= maxUploadRowsInstant();
}

function getMaxUploadFileSize(): int
{
    return (int)(config('app.max_upload_file_size'));
}

function getMaxUploadRows(): int
{
    return (int)(config('app.max_upload_row'));
}

function isMaxAllowedRows($rowCount): bool
{
    return $rowCount <= getMaxUploadRows();
}

function cacheQuery($sql, $timeout = 60) {
    return Cache::remember(md5($sql), $timeout, function() use ($sql) {
        return DB::select(DB::raw($sql));
    });
    //$cache = $this->cacheQuery("SOME COMPLEX JOINS ETC..", 30);
}


function getSolRegions(): \Illuminate\Database\Eloquent\Collection
{
    return SolRegion::all();
}

function geRegionBySol($solId)
{
    return SolRegion::select('region')->where('SolId', $solId)->first();
}

function getSolByRegions($region): \Illuminate\Database\Eloquent\Collection
{
    return SolRegion::where('region', $region)
        ->select('SolId', 'EcName', 'EcAddress')
        ->orderBy('EcName')
        ->get();
}

function getRegions(): \Illuminate\Database\Eloquent\Collection
{
    return SolRegion::select('region')
        ->distinct()->get();
}

function getSols(): \Illuminate\Database\Eloquent\Collection
{
    return SolRegion::select('SolId', 'EcName', 'EcAddress')
        ->orderBy('EcName')
        ->distinct()->get();
}

function getOriginalDetails($terminalId, $rrn, $stan, $pan): object|null
{
    return DB::table('all_recon_data')->where([
        'TerminalIdPostilion' => $terminalId,
        'Pan' => $pan,
        'RetrievalReferenceNumberPostilion' => $rrn,
        'StanPostilion' => $stan,
        'MessageType' => '200'

    ])->first();
}

function checkDuplicateRecon($coverage, $tranDate, $channel = 'FEP', $solId = '', $region = '' ): bool
{
    if(ReconRequest::whereDate('TranDate', '=', $tranDate)
        ->where('Channel', $channel)
        ->where('coverage', 'bank')->exists()){
        Log::info("Reconciliation already Initiated bank wide for $tranDate, Check the report ");
        return true;
    }
    if($coverage === 'region'){
        if(ReconRequest::whereDate('TranDate', '=', $tranDate)
            ->where('Channel', $channel)
            ->where('coverage', 'region')->where('region', $region)->exists()){
            Log::info("Reconciliation already Initiated for $tranDate and $region region, Check the report ");
            return true;
        }
    }
    if($coverage === 'sol'){
        if(ReconRequest::whereDate('TranDate', '=', $tranDate)
            ->where('Channel', $channel)
            ->where('coverage', 'region')->where('solId', $solId)->exists()){
            Log::info("Reconciliation already Initiated for $tranDate and  $solId sol, Check the report ");
            return true;
        }
    }
    if(ReconRequest::whereDate('TranDate', '=', $tranDate)
        ->where('Channel', $channel)
        ->where('coverage', $coverage)->exists()){
        Log::info("Reconciliation already Initiated for $tranDate and $coverage, Check the report ");
        return true;
    }
    return false;
}

function validateReconAndProceed($tranDate, $tranDatePlus, $tranType = '1'): bool
{
    $reconCount = DB::connection('sqlsrv_postilion')->table('post_office_atm_transactions')
        ->whereDate('DateLocal', $tranDate)
        ->where('TranType', $tranType)->where('ResponseCode', '0')
        ->count();

    if($reconCount === 0) {
        return false;
    }

    $reconCountPlus = DB::connection('sqlsrv_postilion')->table('post_office_atm_transactions')
        ->whereDate('DateLocal', $tranDatePlus)
        ->where('TranType', $tranType)->where('ResponseCode', '0')
        ->count();

    if($reconCountPlus === 0) {
        return false;
    }

    return true;

}

function checkNibssInward($batchNumber, $sessionId, $amount): bool
{
    return DB::table('nibss_settlements')
                ->where('Amount', $amount)
                ->where('BatchNumber', $batchNumber)
                ->where('SessionId', $sessionId)
                ->where('Direction', '=','Inward')
                ->exists();
}

function checkNibssOutward($batchNumber, $sessionId, $amount): bool
{
    return DB::table('nibss_settlements')
        ->where('Amount', $amount)
        ->where('BatchNumber', $batchNumber)
        ->where('SessionId', $sessionId)
        ->where('Direction', '=','Outward')
        ->exists();
}

function checkNibssInwarda($batchNumber, $sessionId, $amount){
    return DB::connection('sqlsrv-nibss')->table('NibssOutwardTranBankFbnM')
        ->where('Amount', $amount)
        ->where('BatchNumber', $batchNumber)
        ->where('Status', '=','Successful')
        ->where('SessionId', $sessionId)
        ->exists();
}

function storeRequest($batchNumber, $tranDate, $requestedBy, $channel = 'FEP', $deviceType = 'ATM'): void
{
    ReconRequest::create([
        'BatchNumber' => $batchNumber,
        'Coverage' => 'bank',
        'SolId' => '',
        'Region' => '',
        'Channel' => $channel,
        'DeviceType' => $deviceType,
        'TranDate' => $tranDate,
        'RequestedBy' => $requestedBy
    ]);
}

function updateRecon($batchNumber){
    DB::table('recon_requests')
        ->where('BatchNumber', $batchNumber)
        ->update(
            [
                'Picked' => 'Y',
                'Processed' => 'Y',
                'ProcessedDate' => Carbon::now()
            ]
        );
}


?>
