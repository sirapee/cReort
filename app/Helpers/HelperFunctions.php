<?php
/**
 * Created by PhpStorm.
 * User: Ini-Obong.Udoh
 * Date: 28/11/2017
 * Time: 14:22
 */

namespace app\Helpers;



use App\Models\UserManagementAudit;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use DB;
use COM;
use Excel;
use Sentinel;
use Carbon\Carbon;




class HelperFunctions
{
    public static function createDate($format, $date)
    {
        $date = date_create($date);
        $newDate = date_format($date, $format);
        return $newDate;
    }

    public function downloadExcel($tableName,$reportName,$queryParameters){
        $queryResult = $this->getExcelData($tableName,$queryParameters);
        $this->prepareDownloadData($queryResult,$reportName,Sentinel::getUser()->username)->export('xlsx');
        //$this->prepareExportingData($request)->export('xlsx');
    }

    public function downloadExcelToDirectory($tableName,$reportName,$queryParameters,$path){
        $queryResult = $this->getExcelData($tableName,$queryParameters);
        $this->prepareDownloadData($queryResult,$reportName,Sentinel::getUser()->username)->store('xlsx',$path);
    }

    public static function moveFiles($sourceDirectory, $destinationDirectory){
        try{
            $filesystem = new Filesystem();
            $path = $sourceDirectory;
            // check if the source directory is readable and writable
            if (!$filesystem->isWritable($path) || !$filesystem->isReadable($path)){
                Log::info($path.' not writable');
                    return false;
            }
            // check if the supplied path is a directory
            if($filesystem->isDirectory($path)){
                $filesystem->copyDirectory($path,$destinationDirectory,1);
                //$filesystem->deleteDirectory($path);
                //$filesystem->makeDirectory($path);
                return true;
            }else{
                Log::info($path.' not a directory');
                return;
            }

        }catch (\Exception $e){
            Log::info(' Error moving files from directory');
            return;
        }

    }

    public static function deleteFiles($sourceDirectory){
        try{
            $filesystem = new Filesystem();
            $path = $sourceDirectory;
            // check if the source directory is readable and writable
            if (!$filesystem->isWritable($path) || !$filesystem->isReadable($path)){
                Log::info($path.' not writable');
                return false;
            }
            // check if the supplied path is a directory
            if($filesystem->isDirectory($path)){
                $filesystem->deleteDirectory($path);
                $filesystem->makeDirectory($path);
                return true;
            }else{
                Log::info($path.' not a directory');
                return;
            }

        }catch (\Exception $e){
            Log::info(' Error moving files from directory');
            return;
        }

    }

    public static function generateBatchNumber(){
        //$random = str_pad(mt_rand(11111111,99999999),10,'0',STR_PAD_RIGHT);
        //return $random.now()->timestamp;
        return 'EA'.date('YmdHis');
    }


    public static function pendingVerification($tableName,$parameters,$connection = 'sqlsrv'){
        return DB::connection($connection)->table($tableName)->where($parameters)->get();
    }

    public  static function getPendingUsersByBatch(){
        return DB::table("users")
            ->join('user_management_audit', 'user_management_audit.user_id', '=', 'users.id')
            ->select('function_code','users.id as user_id','users.first_name', 'users.last_name','users.emp_id as employee_id',
                'user_management_audit.inputter','user_management_audit.modified_field_data','user_management_audit.created_at')
            ->where('approved_or_rejected', 'N')
            ->get();

    }

    public static function makerChecker($id,$username,$modelName = 'AccountOpeningAudit'){
        if ($modelName === 'UserManagementAudit'){
            return UserManagementAudit::where('id',$id)
                ->where('inputter',$username)->exists();
        }
        return  false;
    }

    public static function getClientIp()
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

    public static function changeEnvironmentVariable($key,$value)
    {
        $path = base_path('.test');
        $old = '';
        if(is_bool(env($key)))
        {
            $old = env($key)? 'true' : 'false';
        }else{
            //check if key exits
            $searchfor = $key;
            $file = file_get_contents($path);
            //dd(strpos($file, $searchfor));
            if(strpos($file, $searchfor) != 0)
            {
                $old = env($key);
            }
        }
        dd($old);
        if (file_exists($path)) {
            if ($old != ''){

                file_put_contents($path, str_replace(
                    "$key=".$old, "$key=".$value, file_get_contents($path)
                ));
                return true;
            }
        }
        file_put_contents($path, "$key=".$value."\n", FILE_APPEND | LOCK_EX);
        return true;

//        $search = "bob123"
//        $string = file_get_contents("thefile.txt");
//        $string = explode("\n", $string); // \n is the character for a line break
//        if(in_array($search, $string)){
//            echo $search . " is in thefile.txt";
//        } else {
//            echo $search . " is not in thefile.txt";
//        }
    }

    public static function updateEnv($data = [])
    {
        if (!count($data)) {
            return;
        }
        $pattern = '/([^\=]*)\=[^\n]*/';

        $envFile = base_path() . '/.env';
        $lines = file($envFile);
        $newLines = [];
        foreach ($lines as $line) {
            preg_match($pattern, $line, $matches);

            if (!count($matches)) {
                $newLines[] = $line;
                continue;
            }

            if (!key_exists(trim($matches[1]), $data)) {
                $newLines[] = $line;
                continue;
            }
            $value = $data[trim($matches[1])];
            if ($value == trim($value) && strpos($value, ' ') !== false) {
                $value = '"'.$data[trim($matches[1])].'"';
                //dd($value);
            }
            $line = trim($matches[1]) . "={$value}\n";
            $newLines[] = $line;
        }

        $newContent = implode('', $newLines);
        file_put_contents($envFile, $newContent);

        ///append new environment variables
        foreach ($data as $key=>$value){
            $searchfor = $key;
            $file = file_get_contents($envFile);
            //dd(strpos($file, $searchfor));
            if(strpos($file, $searchfor))
            {
                continue;
            }
            if ($value == trim($value) && strpos($value, ' ') !== false) {
                $value = '"'.$value.'"';
                dd($value);
            }

            $newContent = "$key=".$value."\n";
            file_put_contents($envFile, $newContent,FILE_APPEND | LOCK_EX);
        }
        return true;

    }

    public static function getSystemConfig(){
        return SystemConfiguration::first();
    }


    public  static function getPendingCorporateAccountOpening(){
        return CorporateAccountCreationRequest::where('approved', 'N')
            ->get();
    }

    public static function cacheResponse($data)
    {
        $url = request()->url();
        $queryParams = request()->query();

        ksort($queryParams);

        $queryString = http_build_query($queryParams);

        $fullUrl = "{$url}?{$queryString}";

        return Cache::remember($fullUrl, 30/60, function() use($data) {
            return $data;
        });
    }

    public static function  cacheData($key, $array){
        return Cache::remember($key, 22*60, function () use ($array){
            return $array;
        });
    }

    public static function cacheQuery($sql, $timeout = 60) {
        return Cache::remember(md5($sql), $timeout, function() use ($sql) {
            return DB::select(DB::raw($sql));
        });
        //$cache = $this->cacheQuery("SOME COMPLEX JOINS ETC..", 30);
    }

    /**
     * @param array $diff
     * @return array|mixed|string
     */
    public static function arrayKeysToString(array $diff)
    {
        $diff = implode("`, `", array_keys($diff));
        $diff = str_replace("`", "", $diff);
        return $diff;
    }

    public static function arraysToValueString(array $diff)
    {
        $diff = implode("`, `", array_values($diff));
        $diff = str_replace("`", "", $diff);
        return strtoupper($diff);
    }

    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

}
