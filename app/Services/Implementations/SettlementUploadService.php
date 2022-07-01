<?php

namespace App\Services\Implementations;

use App\Contracts\Responses\SettlementUploadResponse;
use App\Helpers\FileUpload;
use App\Http\Requests\SettlementRarRequest;
use App\Services\Interfaces\ISettlementUploadService;
use Illuminate\Support\Facades\Log;

class SettlementUploadService implements ISettlementUploadService
{
    private $response;
    public function __construct()
    {
        $this->response =  new SettlementUploadResponse();
    }

    /**
     * @throws \JsonException
     */
    public function uploadSettlementRarFile(SettlementRarRequest $request): SettlementUploadResponse
    {
        Log::info('Processing Settlement Upload Request '. json_encode($request->all(), JSON_THROW_ON_ERROR));
        try{

            if($request->has('settlementFile')) {
                $file = $request->file('settlementFile');
                $extension = $file->getClientOriginalExtension();
                $rarFile = new FileUpload($file);
                $path = getSettlementFilepath();
                if($extension === 'rar'){
                    //Todo check if file already uploaded
                    $rarFile->extractRarFile($path);
                }

                writeLogs('Dealing with zip files ....');
                writeLogs($path);

                //Dealing with zip files
                $zipFilesPath = $path . "\\"."New folder";
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
                        if(!FileUpload::extractZip2($extractedFilesPath,$zipFilePath)){
                            writeLogs("Extraction failed... ");
                            return $this->response;
                        }
                    }
                    $count++;
                }
            }
            return $this->response;
        }catch(\Exception $e){
            $this->response->responseCode = 907;
            $this->response->responseMessage = $e->getMessage();
            return $this->response;
        }
    }

}
