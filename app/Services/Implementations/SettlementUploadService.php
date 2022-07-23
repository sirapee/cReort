<?php

namespace App\Services\Implementations;

use App\Contracts\Responses\SettlementUploadResponse;
use App\Helpers\FileUpload;
use app\Helpers\HelperFunctions;
use App\Http\Requests\SettlementRarRequest;
use App\Imports\SettlementImport;
use App\Localization\FBNMortGage;
use App\Services\Interfaces\ISettlementUploadService;
use DB;
use DirectoryIterator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettlementUploadService implements ISettlementUploadService
{
    private $response;
    private FBNMortGage $fbnMortGage;

    public function __construct(FBNMortGage $fbnMortGage)
    {
        $this->response =  new SettlementUploadResponse();
        $this->fbnMortGage = $fbnMortGage;
    }

    /**
     * @throws \JsonException
     */
    public function uploadSettlementRarFile(SettlementRarRequest $request): SettlementUploadResponse
    {
        //Todo create an entry to check duplicate processing

        Log::info('Processing Settlement Upload Request '. json_encode($request->all(), JSON_THROW_ON_ERROR));
        try{
            DB::beginTransaction();
            $batchNumber = HelperFunctions::generateBatchNumber();
            $requestedBy = getLoggedInStaffId();
            $this->response->batcnNumber = $batchNumber;
            if($request->has('settlementFile')) {
                $file = $request->file('settlementFile');
                $tranDate = $request->tranDate;
                $coverage = 'bank';
                $channel = $request->channel;
                $solId = '';
                $region = '';

                //Todo check if recon has already been done
                if(checkDuplicateRecon($coverage, $tranDate, $channel, $solId , $region)){
                    $this->response->responseCode = "119";
                    $this->response->responseMessage = "Reconciliation already Initiated, Check the report";
                    return $this->response;
                }

                storeRequest($batchNumber, $tranDate, $requestedBy, 'NIP', 'WEB');
                $folderDate = createDateFromFormat('Y-m-d', $tranDate, 'Ymd');
                $extension = $file->getClientOriginalExtension();
                $originalFilename = $file->getClientOriginalName();
                $rarFile = new FileUpload($file);
                $path = getNibbsSettlementFilepath($folderDate);
                if($extension === 'zip'){
                    //Todo check if file already uploaded
                    $rarFile->extractZip($path);
                }

                writeLogs('Dealing with zip files ....');
                writeLogs($path);
                //Dealing with zip files
                $folderName = str_replace('.'.$extension, '', $originalFilename);
                Log::info("Foldername $folderName");
                $zipFilesPath = $path . "\\".$folderName;
                if(!checkCreateFolder($zipFilesPath)){
                    $this->response->responseMessage = "Error Creating Upload Path";
                    return $this->response;
                }
                writeLogs($zipFilesPath);
                $dir = new \DirectoryIterator($zipFilesPath);
                $count = 1;
                Log::info("Reading Settlement Files from the uploaded zip file...");
                foreach ($dir as $zipFile) {
                    Log::info("Reading File No ". $count);
                    $zipFilePath = $zipFilesPath. "\\".$zipFile;
                    Log::info("Reading File No ". $zipFilePath);
                    if(is_file($zipFilePath)) {
                        Log::info("Reading File No ". $zipFile);
                        $extractedFilesPath = $path . "\\"."Extracted";
                        $this->response->path = $extractedFilesPath ."\\NIP";
                        if(!FileUpload::extractZip2($extractedFilesPath,$zipFilePath)){
                            $this->response->responseMessage = "Extraction failed";
                            writeLogs("Extraction failed... ");
                            return $this->response;
                        }
                        //Read CSV Files
                        $this->readNibssFile($this->response->path, $batchNumber, $requestedBy);
                    }
                    $count++;
                }
            }
            Log::info("Read path  ".$this->response->path);

            //Todo Processing should be entity specific

            $this->fbnMortGage->processNibssOutward($tranDate, $batchNumber, $requestedBy);

            updateRecon($batchNumber);
            DB::commit();
            $this->response->isSuccessful = true;
            $this->response->responseCode = "000";
            $this->response->responseMessage = "Files Uploaded and Extracted Successfully, Processing on going...";
            return $this->response;
        }catch(\Exception $e){
            DB::rollback();
            $this->response->responseCode = 907;
            $this->response->responseMessage = $e->getMessage();
            return $this->response;
        }/**/
    }

    public function readNibssFile($path,$batchNumber, $requestedBy): void
    {

        Cache::put('batchNumber', $batchNumber, now()->addMinutes(30));
        Cache::put('requestedBy', $requestedBy, now()->addMinutes(30));
        $dir = new DirectoryIterator($path);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if($fileinfo->isFile()){
                    if($fileinfo->getExtension() === 'csv'){
                        $filename = $fileinfo->getFilename();
                        //Check for Inward Report
                        $strippedFilename = strtolower(str_replace(' ', '', $filename));
                        //Log::info("StrippedFilename $strippedFilename");
                        if(str_contains($strippedFilename, 'inwardsreport') || str_contains($strippedFilename, 'inwardssuccessful')){
                            //inwards successful
                            Cache::put('type', 'Inward', now()->addSeconds(30));
                            $file = $fileinfo->getPathname();
                            $nibssImport = new SettlementImport;
                            $nibssImport->import($file, null, \Maatwebsite\Excel\Excel::CSV);
                            Cache::forget('type');
                            Log::error(json_encode($nibssImport->errors()));
                        }
                        //Check for Outward Report
                        if(str_contains($strippedFilename, 'outwardsreport') || str_contains($strippedFilename, 'outwardssuccessful')){
                            Cache::put('type', 'Outward', now()->addSeconds(30));
                            $file = $fileinfo->getPathname();
                            $nibssImport = new SettlementImport;
                            $nibssImport->import($file, null, \Maatwebsite\Excel\Excel::CSV);
                            Cache::forget('type');
                            Log::error(json_encode($nibssImport->errors()));
                        }
                    }
                }
            }
        }

    }

}
