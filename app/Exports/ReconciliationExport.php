<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReconciliationExport implements WithMultipleSheets
{
    use Exportable;
    public function __construct(string $tranType = '', $tranDate = '')
    {
        $this->tranType = $tranType;
        $this->tranDate = $tranDate;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $types = ['Reconciled','UnImpacted','Reversed','Settlement'];

        foreach ($types as $type) {
            if($type === 'Reconciled'){
                $sheets[] = new ReconciledExport($this->tranType, $this->tranDate);
            }
            if($type === 'UnImpacted'){
                $sheets[] = new UnImpactedExport($this->tranType, $this->tranDate);
            }
            if($type === 'Reversed'){
                $sheets[] = new ReversedExport($this->tranType, $this->tranDate);
            }
            if($type === 'Settlement'){
                $sheets[] = new SettlementExport($this->tranType, $this->tranDate);
            }

        }

        return $sheets;
    }
}
