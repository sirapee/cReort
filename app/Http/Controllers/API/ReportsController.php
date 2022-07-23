<?php

namespace App\Http\Controllers\API;

use App\Exports\AllEntriesExport;
use App\Exports\AllNibssExport;
use App\Exports\ReconciledExport;
use App\Exports\ReconciliationExport;
use App\Exports\ReversedExport;
use App\Exports\SettlementExport;
use App\Exports\UnImpactedExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ReportsController extends Controller
{
    private ReconciliationHelper $reconciliationHelper;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->reconciliationHelper = new ReconciliationHelper();
    }

    public function dashboard (): \Illuminate\Http\JsonResponse
    {
        return $this->reconciliationHelper->dashboard();
    }

    public function nibssDashboard (): \Illuminate\Http\JsonResponse
    {
        return $this->reconciliationHelper->nibssDashboard();
    }

    public function settlement (Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->reconciliationHelper->settlement($request);
    }

    public function reconciled (Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->reconciliationHelper->reconciled($request);
    }

    public function unImpacted (Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->reconciliationHelper->unImpacted($request);
    }

    public function reversed (Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->reconciliationHelper->reversed($request);
    }

    //NIPS
    public function reconciledNibss (Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->reconciliationHelper->reconciledNibss($request);
    }

    public function reversedNibss (Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->reconciliationHelper->reversedNibss($request);
    }

    //Excel Export

    public function reconciliationExcel (Request $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'ReconciliationFor_'.$request->tranDate;
        return (new ReconciliationExport($request->terminal, $request->tranDate))->download($filename.'.xlsx');
    }

    public function reconciledExcel (Request $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'ReconciledEntriesFor_'.$request->tranDate;
        if($request->has('downloadFormat')){
            $format = $request->downloadFormat;
            if($format === 'csv'){
                return (new ReconciledExport($request->terminal, $request->tranDate))
                    ->download($filename.'.csv', \Maatwebsite\Excel\Excel::CSV);
            }
            if($format === 'pdf'){
                return (new ReconciledExport($request->terminal, $request->tranDate))
                    ->download($filename.'.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }
        }
        return (new ReconciledExport($request->terminal, $request->tranDate))->download($filename.'.xlsx');
    }

    public function unImpactedExcel (Request $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'UnImpactedEntriesFor_'.$request->tranDate;
        if($request->has('downloadFormat')){
            $format = $request->downloadFormat;
            if($format === 'csv'){
                return (new UnImpactedExport($request->terminal, $request->tranDate))
                    ->download($filename.'.csv', \Maatwebsite\Excel\Excel::CSV);
            }
            if($format === 'pdf'){
                return (new UnImpactedExport($request->terminal, $request->tranDate))
                    ->download($filename.'.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            }
        }
        return (new UnImpactedExport($request->terminal, $request->tranDate))->download($filename.'.xlsx');
    }

    public function reversedExcel (Request $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'ReversedEntriesFor_'.$request->tranDate;
        if($request->has('downloadFormat')){
            $format = $request->downloadFormat;
            if($format === 'csv'){
                return (new ReversedExport($request->terminal, $request->tranDate))
                    ->download($filename.'.csv', \Maatwebsite\Excel\Excel::CSV);
            }
            if($format === 'pdf'){
                return (new ReversedExport($request->terminal, $request->tranDate))
                    ->download($filename.'.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            }
        }
        return (new ReversedExport($request->terminal, $request->tranDate))->download($filename.'.xlsx');
    }

    public function settlementExcel (Request $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'SettlementEntriesFor_'.$request->tranDate;
        if($request->has('downloadFormat')){
            $format = $request->downloadFormat;
            if($format === 'csv'){
                return (new SettlementExport($request->terminal, $request->tranDate))
                    ->download($filename.'.csv', \Maatwebsite\Excel\Excel::CSV);
            }
            if($format === 'pdf'){
                return (new SettlementExport($request->terminal, $request->tranDate))
                    ->download($filename.'.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            }
        }
        return (new SettlementExport($request->terminal, $request->tranDate))->download($filename.'.xlsx');
    }

    public function allExcel (Request $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'AllEntriesFor_'.$request->tranDate;
        if($request->has('downloadFormat')){
            $format = $request->downloadFormat;
            if($format === 'csv'){
                return (new AllEntriesExport($request->terminal, $request->tranDate))
                    ->download($filename.'.csv', \Maatwebsite\Excel\Excel::CSV);
            }
            if($format === 'pdf'){
                return (new AllEntriesExport($request->terminal, $request->tranDate))
                    ->download($filename.'.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            }
        }
        return (new AllEntriesExport($request->terminal, $request->tranDate))->download($filename.'.xlsx');
    }

    public function allNibssExcel (Request $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'NIPReconciliationFor_'.$request->tranDate;
        if($request->has('downloadFormat')){
            $format = $request->downloadFormat;
            if($format === 'csv'){
                return (new AllNibssExport($request->batchNumber, $request->tranDate))
                    ->download($filename.'.csv', \Maatwebsite\Excel\Excel::CSV);
            }
            if($format === 'pdf'){
                return (new AllNibssExport($request->batchNumber, $request->tranDate))
                    ->download($filename.'.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            }
        }
        return (new AllNibssExport($request->batchNumber, $request->tranDate))->download($filename.'.xlsx');
    }
}
