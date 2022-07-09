<?php

namespace App\Exports;

use App\Models\ReconEntry;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReconciledExport implements FromQuery , WithHeadings
{
    use Exportable;
    public function __construct(string $tranType = '', $tranDate = '')
    {
        $this->tranType = $tranType;
        $this->tranDate = $tranDate;
    }


    public function query()
    {
        [$region, $solId] = getSolRegion();

        $query  = ReconEntry::query();

        if(!empty($region)){
            $query = $query->where('Region', $region);
        }

        if(!empty($solId)){
            $query = $query->where('SolId', $solId);
        }/**/
        if(!empty($this->tranDate)){
            $query = $query->whereDate('DateLocal', $this->tranDate);
        }

        if(!empty($this->tranType)){
            $query = $query->where('TranType', $this->tranType);
        }
        return $query;
    }

    public function headings(): array
    {
        return [
            'Id' ,'BatchNumber' ,'SolId' ,'Region' ,'Pan' ,'AccountNumber' ,'RetrievalReferenceNumberPostilion' ,'RetrievalReferenceNumberFinacle' ,
            'StanPostilion' ,'StanFinacle' ,'TranType' ,'DateLocal' ,'AmountPostilion' ,'AmountFinacle' ,'TranIdFinacle' ,'TerminalIdPostilion' ,
            'TerminalIdFinacle' ,'MessageType' ,'IssuerName' ,'RequestDate' ,'EntryDate' ,'ResponseCode' ,'TriggeredBy' ,'DebitAccount' ,'CreditAccount' ,
            'PostingAmount' ,'PostingNarration' ,'PostingStan' ,'ValueDate' ,'ThreadId' ,'Priority' ,'Picked' ,'Posted' ,'ApprovedForPosting' ,
            'PostingResponseCode' ,'PostingResponseMessage' ,'PostingTranId' ,'PostingDate' ,'CreatedAt' ,'UpdatedAt' ,'Status' ,'AccountNumberFinacle' ,
            'AccountNameFinacle' ,'NarrationFinacle' ,'TranCurrencyFinacle'
        ];
    }
}
