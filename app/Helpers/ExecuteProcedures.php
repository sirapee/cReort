<?php
/**
 * Created by PhpStorm.
 * User: ini-obong.udoh
 * Date: 20/10/2017
 * Time: 07:05
 */

namespace app\Helpers;
use App\loanModels\LoanJob;
use App\loanModels\DailyJobLog;
use App\loanModels\OverdueLoans;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;


class ExecuteProcedures
{
    public $accountNumber;
    public $startTime;
    public $endTime;

    public function __construct($accounNumber)
    {
        $this->accountNumber = $accounNumber;
    }


    public static function runDailyInterestRun(){
        //DB::statement('call sp_clientupdate("108", "Sandeep","0999999999","","","sandeep@gmail.com","","","","","","",)');
        try{
            $startTime = Carbon::now();
//            $value = DB::statement(DB::raw("DECLARE	@return_value int
//                EXEC	@return_value = [dbo].[dailyInterestRun]
//                SELECT	'Return Value' = @return_value;"));
//
//            if($value == 0){
//                $status = 'S';
//            }else{
//                $status = 'F';
//            }

            if(DB::statement('exec nanoloan.dbo.dailyInterestRun')){
                $status = 'S';
            }else{
                $status = 'F';
            }
            $endTime = Carbon::now();
            $data = [
                'job_name' => 'Daily Interest Run',
                'job_description' => 'Job for Interest Accrural',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status
            ];
            DailyJobLog::insert($data);
            return true;
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return false;
        }
    }

    public function accountInterestRun(){
        //DB::statement('call sp_clientupdate("108", "Sandeep","0999999999","","","sandeep@gmail.com","","","","","","",)');
        try{

            $startTime = Carbon::now();
            if(DB::statement('exec nanoloan.dbo.accountInterestRun("'.$this->accountNumber.'")')){
                $status = 'S';
            }else{
                $status = 'F';
            }
            $endTime = Carbon::now();
            $data = [
                'job_name' => 'Account Interest Run',
                'job_description' => 'Job for Interest Accrural on a Single account',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status
            ];
            DailyJobLog::insert($data);

            return true;
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return false;
        }
    }

    public static function moveToTransactionTableRun(){
        //DB::statement('call sp_clientupdate("108", "Sandeep","0999999999","","","sandeep@gmail.com","","","","","","",)');
        try{
            $startTime = Carbon::now();
                if(DB::statement('exec nanoloan.dbo.moveToTranTable')){
                $status = 'S';
            }else{
                $status = 'F';
            }
            ;
            $endTime = Carbon::now();
            $data = [
                'job_name' => 'Move to Transaction Table Run',
                'job_description' => 'Job to move accounts and outstanding to transaction table',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status
            ];
            DailyJobLog::insert($data);
            return true;
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return false;
        }
    }
    public function accountMoveToTransactionTableRun(){
        //DB::statement('call sp_clientupdate("108", "Sandeep","0999999999","","","sandeep@gmail.com","","","","","","",)');
        try{
            $startTime = Carbon::now();
            if(DB::statement('exec nanoloan.dbo.accountMoveToTranTable("'.$this->accountNumber.'")')){
                $status = 'S';
            }else{
                $status = 'F';
            }
            ;
            $endTime = Carbon::now();
            $data = [
                'job_name' => 'Move a single account to Transaction Table Run',
                'job_description' => 'Job to move account and outstanding to transaction table',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status
            ];
            DailyJobLog::insert($data);
            return true;
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return false;
        }
    }

    public static function overdueDailyInterestRun(){
        //DB::statement('call sp_clientupdate("108", "Sandeep","0999999999","","","sandeep@gmail.com","","","","","","",)');
        try{
            $startTime = Carbon::now();
            if(DB::statement('exec nanoloan.dbo.overdueDailyInterestRun')){
                $status = 'S';
            }else{
                $status = 'F';
            }
            ;
            $endTime = Carbon::now();
            $data = [
                'job_name' => 'Overdue daily interest Run',
                'job_description' => 'Job to calculate overdue on accounts',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status
            ];
            DailyJobLog::insert($data);
            return true;
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return false;
        }
    }

    public function accountOverdueInterestRun(){
        //DB::statement('call sp_clientupdate("108", "Sandeep","0999999999","","","sandeep@gmail.com","","","","","","",)');
        try{
            $startTime = Carbon::now();
            if(DB::statement('exec nanoloan.dbo.accountMoveToTranTable("'.$this->accountNumber.'")')){
                $status = 'S';
            }else{
                $status = 'F';
            }
            $endTime = Carbon::now();
            $data = [
                'job_name' => 'Account Overdue interest Run',
                'job_description' => 'Job to calculate overdue on an account',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status
            ];
            DailyJobLog::insert($data);
            return true;
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return false;
        }
    }

}