<?php

namespace App\Exports;

use App\Models\UnImpactedEntry;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UnImpactedExport implements FromQuery , WithHeadings
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

        $query  = UnImpactedEntry::query();

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
            'Id' ,'BatchNumber' ,'SolId' ,'Region' ,'Pan' ,'AccountNumber' ,'RetrievalReferenceNumberPostilion' ,'StanPostilion' ,'TranType' ,
            'DateLocal' ,'AmountPostilion' ,'TerminalIdPostilion' ,'MessageType' ,'IssuerName' ,'RequestDate' ,'ResponseCode' ,'TriggeredBy' ,
            'DebitAccount' ,'CreditAccount' ,'PostingAmount' ,'PostingNarration' ,'PostingStan' ,'ValueDate' ,'ThreadId' ,'Priority' ,'Status' ,
            'Picked' ,'Posted' ,'ApprovedForPosting' ,'ApprovedForPostingBy' ,'ApprovedDate' ,'PostingResponseCode' ,'PostingResponseMessage' ,
            'PostingTranId' ,'PostingDate' ,'CreatedAt' ,'Updated At'
        ];
    }
}

