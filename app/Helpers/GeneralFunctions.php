<?php
/**
 * Created by PhpStorm.
 * User: Ini-Obong.Udoh
 * Date: 28/11/2017
 * Time: 14:22
 */

namespace app\Helpers;

use App\RunningProcess;
use Illuminate\Support\Facades\Log;
use DB;
use Excel;
use Sentinel;
use App\soap\nusoap_client;



class GeneralFunctions
{
    public static function createDate($format, $date)
    {
        $date = date_create($date);
        $newDate = date_format($date, $format);
        return $newDate;
    }



    private function prepareDownloadData($expiryDate,$reportName,$author) {

        $transactions = $this->getDownloadData($expiryDate);
        return Excel::create($reportName, function($excel) use($transactions, $expiryDate, $author) {
            // Set the title
            $excel->setTitle('Card Maintenance for '. $expiryDate);

            // Chain the setters
            $excel->setCreator($author)
                ->setCompany('Heritage Bank');

            // Call them separately
            $excel->setDescription('Card Maintenance');

            $excel->sheet('Card Maintenance', function($sheet) use($transactions) {

                $sheet->fromArray($transactions);
            });
        });
    }

    private function getDownloadData($expiryDate) {
        return DB::connection('sqlsrv2')->table('month_card_charge')
            ->where('expiry_date', '>', $expiryDate)
            ->get()
            ->map(function ($item, $key) {
                return (array) $item;
            })
            ->all();
    }

    private function checkChargeCount($value,$month,$year)
    {
        $countCheck1 = DB::connection('sqlsrv')->table('monthly_card_charges')
            ->whereMonth('month_charge', '=', $month)
            ->whereyear('month_charge', '=', $year)
            ->where('account_number', $value->account_id)
            ->count();
        $countCheck2 = DB::connection('sqlsrv')->table('monthly_card_charges_history')
            ->whereMonth('month_charge', '=', $month)
            ->whereyear('month_charge', '=', $year)
            ->where('account_number', $value->account_id)
            ->count();
        return $countCheck1 + $countCheck2;
    }

    public function callServicePost($url,$body){

        $client = new \GuzzleHttp\Client();
        //$response = $client->createRequest("POST", $url, ['ldap' => ['root','root'],'body'=>$body]);
        $response = $client->request("POST", $url, ['form_params'=>$body]);
        $response = json_decode($response->getBody(), TRUE);
        return $response;

    }

    public function callServiceGet($url){

        $client = new \GuzzleHttp\Client(['verify' => false]);
        //$response = $client->createRequest("POST", $url, ['ldap' => ['root','root'],'body'=>$body]);
        $response = $client->request("GET", $url);
        $response = json_decode($response->getBody(), TRUE);
        return $response;

    }

    public function writeToFile($path,$content){
        $format = 'Y/m/d H:i:s';
        $now = date($format );
        file_put_contents(($path), $now.' '.$content."\n", FILE_APPEND | LOCK_EX);
        return true;
    }



    public function storeRunningJobs($jobId,$processType,$settlementType){
        $format = 'Y/m/d H:i:s';
        $now = date($format );
        $id = DB::table('running_processes')->insertGetId(
            [
                'job_id' => $jobId,
                'process_type' => $processType,
                'settlement_type' => $settlementType,
                'created_at' => $now
            ]
        );
        return $id;
    }

    public function updateJobStatus(){
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


    public function jobStatus(){
        $this->updateJobStatus();
        $runningJobs = RunningProcess::whereIn('process_type',['Reconciliation','Settlement'])
            ->orderBy('created_at','desc')->paginate(5);
        return $runningJobs;
    }



    public function checkRunningProcess($processType,$settlementType){
        return DB::table('running_processes')
            ->where('process_type',$processType)
            ->where('settlement_type', $settlementType)
            ->where('status','R')
            ->count();
    }

    public static function writeLogs($message){
        if (Sentinel::check()){
            $user = Sentinel::getUser()->username;
        }else{
            $user = 'anonymous';
        }
        Log::info($user."\n" . $message);
        return true;
    }

    public function callSoapService($wsld,$param,$requestMethod,$responseMethod){
        //return $this->callService($this->data['wsdl'],$param,'PostTransaction','PostTransactionResult');
        $client = new nusoap_client($wsld, 'wsdl');
        $client->soap_defencoding='utf-8';
        $err = $client->getError();
        if ($err) {
            Log::info($err);
            dd('Here');
        }
        $result = $client->call($requestMethod, array('parameters' => $param), '', '', false, true);
        // Check for a fault
        if ($client->fault) {
            //Log::info($result);dd('Here');
            return false;
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                Log::info($err);
                return false;
            } else {
                Log::info($result);
                return $result[$responseMethod];
            }
        }
    }


    public static function configPendingVerification($id,$connection = 'sqlsrv',$tableName = 'channel_configurations_mod'){
        if ($tableName == 'AuthUser_mod')
            return  DB::connection($connection)->table($tableName)->where('oldId',$id)->count();
        return  DB::connection($connection)->table($tableName)->where('old_id',$id)
            ->count();
    }

}