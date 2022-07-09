<?php

namespace App\Exports;

use App\Models\AllReconData;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AllEntriesExport implements FromQuery , WithHeadings
{
    use Exportable;
    public function __construct(string $tranType = '', $tranDate = '')
    {
        $this->tranType = $tranType;
        $this->tranDate = $tranDate;
    }


    public function query()
    {
        //[$region, $solId] = getSolRegion();

        $query  = AllReconData::query();

        /*if(!empty($region)){
            $query = $query->where('Region', $region);
        }

        if(!empty($solId)){
            $query = $query->where('SolId', $solId);
        }*/
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
            'StanPostilion' ,'AmountPostilion' ,'AmountFinacle' ,'TranIdFinacle' ,'StanFinacle' ,'TranType' ,'DateLocal' ,'TerminalIdPostilion' ,
            'TerminalIdFinacle' ,'MessageType' ,'IssuerName' ,'AccountNumberFinacle' ,'AccountNameFinacle' ,'NarrationFinacle' ,'PanFinacle' ,
            'ValueDateFinacle' ,'TranDateFinacle' ,'TranCurrencyFinacle' ,'RequestDate' ,'EntryDate' ,'ResponseCode' ,'TriggeredBy' ,'Status' ,
            'IsReversed' ,'ReversalId' ,'CreatedAt' ,'UpdatedAt'
        ];
    }
}

