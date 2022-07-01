<?php
/**
 * Created by PhpStorm.
 * User: Ini-Obong.Udoh
 * Date: 09/10/2017
 * Time: 08:00
 */

namespace app\Helpers;
use App\Models\Upload;
use Illuminate\Support\Facades\Log;
use RarArchive;
use Sentinel;
use App\User;


class FileUpload
{
    protected $user;
    protected $upload;
    protected $files;
    protected $destination;

    public function __construct($files)
    {
        $this->files = $files;

    }

    public function uploadFile($destination){

        return $this->getAndUploadFile($this->files,$destination );

    }

    public function uploadFiles($destination){
        //$files = $request->file('myFiles');
        $fileCount = count($this->files);
        //dd($this->files);
        $uploadCount = 0;
        return $this->getAndUploadFiles($this->files,$destination, $fileCount, $uploadCount);

    }

    public function extractZip($destination){
        $filename = $this->uploadNew($this->files,$destination);
        $zip = new \ZipArchive;
        if ($zip->open($destination.'\\'.$filename) === TRUE) {
            $zip->extractTo($destination);
            $zip->close();
            chdir($destination);
            unlink($filename);
            return true;
        } else {
            return false;
        }
    }

    public static function extractZip2($destination, $filePath){
        $zip = new \ZipArchive;
        if ($zip->open($filePath) === TRUE) {
            $zip->extractTo($destination);
            $zip->close();
            chdir($destination);
            //unlink($filePath);
            return true;
        } else {
            return false;
        }
    }

    function extractRarFile($destination){
        $filename = $this->uploadNew($this->files,$destination);
        $filePath = $destination.'\\'.$filename;
        $archive = RarArchive::open($filePath);

        $entries = $archive->getEntries();
        foreach ($entries as $entry) {
            $entry->extract($destination);
        }
        $archive->close();
    }

    public function zipFile($file){
        $zip = new \ZipArchive();
        $zip->addFile($file);
        return true;
    }

    public function uploadNew($file,$destination){
        $destinationPath = $destination; // upload folder in public directory
        $fileName = $file->getClientOriginalName();
        $file->move($destinationPath, $fileName);
        return $fileName;
    }


    public static function checkFile($filename){
        $check = Upload::where('filename',trim($filename))->count();
        if ($check < 1) {
            return true;
        }
        return false;
    }

    /**
     * @param $files
     * @param $fileCount
     * @param $uploadCount
     * @return mixed
     */
    private function getAndUploadFiles($files,$destination, $fileCount, $uploadCount)
    {
        try{

            foreach ($files as $file) {
                $destinationPath = $destination; // upload folder in public directory
                $fileName = $file->getClientOriginalName();
                $fileSuccess = $file->move($destinationPath, $fileName);
                $uploadCount++;


                //using laravel save method
                /**
                 * $file->storeAs($destination, $filename);
                 *
                 */

                //save into the
                //$file->getClientsize() get file size
                //$file->getaTime() last accessed time
                //$file->getmTime()  last modified timw
                //$file->getcTime() creation time
                $user = Sentinel::check();
                $extension = $file->getClientOriginalExtension();
                $upload = new Upload();
                $upload->mime = $file->getClientmimeType();
                $upload->original_filename = $fileName;
                $upload->filename = $file->getFileName() . '.' . $extension;
                $user->uploads()->save($upload);

            }
            return $uploadCount;
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return false;
        }

    }

    private function getAndUploadFile($files,$destination)
    {
        $file = $files;
        $destinationPath = $destination; // upload folder in public directory
        $fileName = $file->getClientOriginalName();
        $fileSuccess = $file->move($destinationPath, $fileName);
        //$user =Sentinel::check();
        $extension = $file->getClientOriginalExtension();
        $upload = new Upload();
        $upload->mime = $file->getClientmimeType();
        $upload->original_filename = $fileName;
        $upload->filename = $fileName;
        //$user->uploads()->save($upload);
        //return $user;
        return true;
    }
}
