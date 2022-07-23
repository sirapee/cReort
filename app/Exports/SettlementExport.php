<?php

namespace App\Exports;

use App\Models\SettlementEntry;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SettlementExport implements FromQuery , WithHeadings, WithTitle
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

        $query  = SettlementEntry::query();

        if(!empty($region)){
            $query = $query->where('Region', $region);
        }

        if(!empty($solId)){
            $query = $query->where('SolId', $solId);
        }/**/
        if(!empty($this->tranDate)){
            $query = $query->whereDate('EntryDate', $this->tranDate);
        }

        if(!empty($this->tranType)){
            $query = $query->where('TerminalType', $this->tranType);
        }
        return $query;
    }

    public function headings(): array
    {
        return [
            'Id' ,'BatchNumber' ,'SolId' ,'Region' ,'PanFinacle' ,'RetrievalReferenceNumberFinacle' ,'StanFinacle' ,'AmountFinacle' ,'TranIdFinacle' ,
            'TerminalIdFinacle' ,'AccountNumberFinacle' ,'AccountNameFinacle' ,'NarrationFinacle' ,'TranCurrencyFinacle' ,'TerminalType' ,
            'ValueDateFinacle' ,'TranDateFinacle' ,'EntryDate' ,'TriggeredBy' ,'DebitAccount' ,'CreditAccount' ,'PostingAmount' ,'PostingNarration' ,
            'PostingStan' ,'ValueDate' ,'ThreadId' ,'Priority' ,'Picked' ,'Posted' ,'ApprovedForPosting' ,'ApprovedForPostingBy' ,'ApprovedDate' ,
            'PostingResponseCode' ,'PostingResponseMessage' ,'PostingTranId' ,'Status' ,'PostingDate' ,'CreatedAt' ,'UpdatedAt'
        ];
    }

    public function title(): string
    {
        return 'SettlementFor_'.$this->tranDate;
    }
}
