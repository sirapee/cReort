<?php
/**
 * Created by PhpStorm.
 * User: Ini-Obong.Udoh
 * Date: 26/10/2017
 * Time: 10:17
 */

namespace app\Helpers;


use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\Log;
use DB;


class LogTransactions
{
    public $data;

    public function __construct($data)
    {
        $this->$data = $data;
    }

    public static function logNewTransaction($data){
        $data = [
            'account_number' => $data['debitAccount'],
            'sms_cost' => $data['amount'],
            'income' => $data['fee1'],
            'vat' => $data['fee2'],
            'month_charge' => $data['debitAccount'],
            'narration' => $data['narration'],
            'stan' => $data['refNo'],
            'sol_id' => $data['solId'],
            'system_date_time' => $data['valueDate'],
            'credit_gl' => $data['creditAccount'],
            'trnsf_date' => $data['trnsf_date'],
            'month_charge' => $data['month_charge'],
            'created_at' =>  date('Y-m-d H:m:i' ),
            'created_by' =>   $data['created_by'],
            'created_date' =>  $data['created_date'],
            'thread_id' =>  $data['thread_id']
        ];

        try{
            if($id = DB::connection('sqlsrv')->table('monthly_sms_charges')->insertGetId($data)){
                return $id;
            }
            return false;
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return false;
        }
    }

    public static function updateTransaction($id,$response,$posted = 'N',$tranDate = null,$lienPlaced = 'N',$lienId = null){
        if ($response['responseCode'] == 0){
            $data = [
                'response_code' => $response['responseCode'],
                'transaction_id' => $response['transactionId'],
                'response_message' => $response['responseMessage'],
                'transaction_date' => $tranDate,
                'lien_placed' => $lienPlaced,
                'lien_id' => $lienId,
                'posted' => $posted,
                'updated_at' =>  date('Y-m-d H:m:i' )
            ];
        }else{
            $data = [
                'response_code' => $response['responseCode'],
                'response_message' => $response['responseMessage'],
                'transaction_date' => $tranDate,
                'lien_placed' => $lienPlaced,
                'lien_id' => $lienId,
                'posted' => 'N',
                'updated_at' =>  date('Y-m-d H:m:i' )
            ];
        }

        try{
            DB::connection('sqlsrv')->table('monthly_sms_charges')
                ->where('id', $id)
                ->update($data);
            $param1 = $id;
            //move posted transactions to history table
            DB::connection('sqlsrv')->statement("exec moveSMSTransactionsToHistory @id = $param1");
            return true;
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return false;
        }
    }

    public static function formatInquiryResponse($response,$terminalType){
        // dd($response);
        $formatted = [];
        $responseCode = trim(substr($response,0,1),'-');
        $results = print_r(substr($response,strpos($response,'Received'),strlen($response)), true);
        $filename = str_random(12) . '.txt';
        chdir('inquiries/');
        file_put_contents($filename, print_r($results, true));
        $lineCount = 0;
        $file = fopen("$filename", "r");
        //dd($file);
        while(!feof($file)) {
            $contents = fgets($file);
            $check1 = substr (trim($contents,'"'),0,8);
            $check2 = substr (trim($contents,'"'),0,9);
            if ($terminalType == 'BLR'){
                if ($check2 == 'Field 127') {
                    $output = trim(substr($contents, strpos($contents, '::') + 2, strlen($contents)));
                    $formatted['Field 127'] = $output;
                    $array = explode('*', $output);
                    //dd(substr($array[0],1, strlen($array[0]) -2));
                    $availableBalance = substr($array[0],1, 14) . '.' . substr($array[0], -2);
                    $availableBalance = number_format($availableBalance,2,'.',',');
                    if(substr($array[0],0, 1 ) == '-'){
                        $availableBalance = '- ' .$availableBalance;
                    }
                    $ledgerbalance = substr($array[1],1, 14) . '.' . substr($array[1], -2);
                    $ledgerbalance = number_format($ledgerbalance,2,'.',',');
                    if(substr($array[1],0, 1 ) == '-'){
                        $ledgerbalance = '- ' .$ledgerbalance;
                    }
                    $details = [
                        'responseCode' => $responseCode,
                        'availableBalance' => $availableBalance,
                        'ledgerbalance' => $ledgerbalance,
                    ];
                    return $details;
                }
            }
            else{
                if($check1 == 'Field 48'){
                    $output = trim(substr($contents,strpos($contents,'::')+2,strlen($contents)));
                    $formatted['Field 48'] = $output;
                    //return response()->json($formatted, 201);
                }
                elseif ($check2 == 'Field 125'){
                    $output = trim(substr($contents,strpos($contents,'::')+2,strlen($contents)));
                    $formatted['Field 125'] = $output;
                    //return response()->json($formatted, 201);
                }
                elseif ($check2 == 'Field 126'){
                    $output = trim(substr($contents,strpos($contents,'::')+2,strlen($contents)));
                    $formatted['Field 126'] = $output;
                    //return response()->json($formatted, 201);
                }
                elseif ($check2 == 'Field 127'){
                    $output = trim(substr($contents,strpos($contents,'::')+2,strlen($contents)));
                    $formatted['Field 127'] = $output;
                    //return response()->json($formatted, 201);
                }
            }

            $lineCount++;
        }

        $details = [
            'responseCode' => $responseCode,
            'responseMessage' => $formatted,
        ];
        return $details;
    }

    public static function formatPostTransaction($response){

        $breakDown = explode('|',$response);
        if (trim($breakDown[0],'-') == 0){
            $tranId = ltrim($breakDown[1],'7');
            $tranId = trim($breakDown[1],'::');
            if ( trim($breakDown[1])== 'n object.'){
                $tranId = 'S'.random_int(100000,9999999);
            }
            $details = [
                'responseCode' => trim($breakDown[0],'-'),
                'transactionId' => $tranId,
                'responseMessage' => 'Approved',
                'referenceId' =>  trim($breakDown[2],'')
            ];
        }else{
            $details = [
                'responseCode' => trim($breakDown[0],'-'),
                'responseMessage' => trim($breakDown[1],'::'),
                'transactionId' => '',
                'referenceId' =>  trim($breakDown[2],'')
            ];
        }

        return $details;
    }


}