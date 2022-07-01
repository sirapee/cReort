<?php

namespace App\Imports;

use App\Models\Settlement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Throwable;

class SettlementImport implements ToCollection ,  SkipsOnError
{
    Use Importable, SkipsErrors;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function collection(Collection $collection)
    {
        foreach ($collection as $row)
        {
            if (!isset($row[2]))
                continue;
            if(strlen($row[2]) < 30)
                continue;
            $channel = trim($row[1]);
            $sessionId = trim($row[2], "'");
            $transactionType= trim($row[3]);
            $response = trim($row[4]);
            $amount = trim($row[5]);
            $transactionTime = trim($row[6], "'");
            $sourceInstitution =  trim($row[7]);
            $senderName =  $transactionType= trim($row[8]);
            $destinationBank = trim($row[9]);
            $destinationAccountName = trim($row[10]);
            $destinationAccountNumber =  trim($row[11]);
            $narration = trim($row[12]);
            $paymentReference =  trim($row[13]);
            Log::info($sessionId . ' => '. $amount);
            /*Settlement::create([
                'name' => $row[0],
            ]);*/
        }
    }


    public function onError(Throwable $e)
    {
        // TODO: Implement onError() method.
    }
}
