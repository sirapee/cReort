<?php

namespace App\Exports;

use App\Models\ReversedEntry;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReversedExport implements FromQuery , WithHeadings, WithTitle
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

        $query  = ReversedEntry::query();

        /**/if(!empty($region)){
            $query = $query->where('Region', $region);
        }

        if(!empty($solId)){
            $query = $query->where('SolId', $solId);
        }
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
            'Id' ,'BatchNumber' ,'SolId' ,'Region' ,'Pan' ,'DateLocal' ,'AccountNumber' ,'RetrievalReferenceNumberPostilion' ,
            'RetrievalReferenceNumberFinacle' ,'StanPostilion' ,'StanFinacle' ,'TranType' ,'RequestDate' ,'AmountPostilion' ,
            'AmountFinacle' ,'TranIdFinacle' ,'TerminalIdPostilion' ,'TerminalIdFinacle' ,'MessageType' ,'IssuerName' ,'Status' ,
            'EntryDate' ,'ResponseCode' ,'TriggeredBy' ,'CreatedAt' ,'UpdatedAt'
        ];
    }

    public function title(): string
    {
        return 'ReversedFor_'.$this->tranDate;
    }
}
