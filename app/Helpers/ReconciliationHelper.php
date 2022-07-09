<?php

namespace App\Http\Controllers\API;

use App\Models\AllReconData;
use App\Models\ReconEntry;
use App\Models\ReconRequest;
use App\Models\ReversedEntry;
use App\Models\SettledEntry;
use App\Models\SettlementEntry;
use App\Models\UnImpactedEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ReconciliationHelper
{

    public function dashboard(): \Illuminate\Http\JsonResponse
    {
        [$region, $solId] = getSolRegion();

        //Todo based on role
        $allQuery = AllReconData::query();
        $settledQuery = SettledEntry::query();
        $reversedQuery = ReversedEntry::query();
        $settlementQuery = SettlementEntry::query();
        $unImpactedQuery = UnImpactedEntry::query();
        $reconciledQuery = ReconEntry::query();
        $allGet = ReconRequest::query();
        $all = [];

        if(!empty($solId)){
            Log::info("Admin here");
            //Counts
            $allCount = $allQuery->where('SolId', $solId)->count();
            $settledCount = $settledQuery->where('SolId', $solId)->count();
            $reversedCount = $reversedQuery->where('SolId', $solId)->count();
            $settlementCount = $settlementQuery->where('SolId', $solId)->count();
            $unImpactedCount = $unImpactedQuery->where('SolId', $solId)->count();
            $reconciledCount = $reconciledQuery->where('SolId', $solId)->count();

            $last10Settled = $settledQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->where('SolId', $solId)
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();

            $last10Reversed = $reversedQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->where('SolId', $solId)
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();

            $last10Settlement = $settlementQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, TranDateFinacle'))
                ->where('SolId', $solId)
                ->groupBy('BatchNumber', 'TranDateFinacle', 'status')
                ->get();

            $last10UnImpacted = $unImpactedQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->where('SolId', $solId)
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();
            $last10Reconciled = $reconciledQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->where('SolId', $solId)
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();

            //Data
            $all = $allGet->where('SolId', $solId)->get();

        }elseif (!empty($region)){
            $allCount = $allQuery->where('Region', $region)->count();
            $settledCount = $settledQuery->where('Region', $region)->count();
            $reversedCount = $reversedQuery->where('Region', $region)->count();
            $settlementCount = $settlementQuery->where('Region', $region)->count();
            $unImpactedCount = $unImpactedQuery->where('Region', $region)->count();
            $reconciledCount = $reconciledQuery->where('Region', $region)->count();

            $last10Settled = $settledQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->where('Region', $region)
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();

            $last10Reversed = $reversedQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->where('Region', $region)
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();

            $last10Settlement = $settlementQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, TranDateFinacle'))
                ->where('Region', $region)
                ->groupBy('BatchNumber', 'TranDateFinacle', 'status')
                ->get();

            $last10UnImpacted = $unImpactedQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->where('Region', $region)
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();

            $last10Reconciled = $reconciledQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->where('Region', $region)
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();
        }else{
            $allCount = $allQuery->count();
            $reconciledCount = $reconciledQuery->count();
            $settledCount = $settledQuery->count();
            $reversedCount = $reversedQuery->count();
            $settlementCount = $settlementQuery->count();
            $unImpactedCount = $unImpactedQuery->count();
            $all = $allGet->get();

            $last10Settled = $settledQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();

            $last10Reversed = $reversedQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();

            $last10Settlement = $settlementQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, TranDateFinacle'))
                ->groupBy('BatchNumber', 'TranDateFinacle', 'status')
                ->get();

            $last10UnImpacted = $unImpactedQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();

            $last10Reconciled = $reconciledQuery
                ->select(DB::raw('count(*) as user_count, status, BatchNumber, RequestDate'))
                ->groupBy('BatchNumber', 'RequestDate', 'status')
                ->get();
        }


        $data = [
            'allRecon' => $all,
            'allProcessedCount' => $allCount,
            'settledCount' => $settledCount,
            'reversedCount' => $reversedCount,
            'settlementCount' => $settlementCount,
            'unImpactedCount' => $unImpactedCount,
            'reconciledCount' => $reconciledCount,
            'last10Settled' => $last10Settled,
            'last10Reversed' => $last10Reversed,
            'last10Settlement' => $last10Settlement,
            'last10UnImpacted' => $last10UnImpacted,
            'last10Reconciled' => $last10Reconciled
        ];


        return response()->json($data);


    }

    public function reconciled (Request $request): \Illuminate\Http\JsonResponse
    {
        [$region, $solId] = getSolRegion();
        $tranDate = $request->tranDate;
        $tranType = $request->terminal;
        $settledQuery = ReconEntry::query();
        if(!empty($region)){
            $settledQuery = $settledQuery->where('Region', $region);
        }

        if(!empty($solId)){
            $settledQuery = $settledQuery->where('SolId', $solId);
        }

        if(!empty($tranDate)){
            $settledQuery = $settledQuery->whereDate('DateLocal', $tranDate);
        }

        if(!empty($tranType)){
            $settledQuery = $settledQuery->where('TranType', $tranType);
        }

        $settled = $settledQuery->paginate(15);

        return response()->json($settled);
    }

    public function settlement (Request $request): \Illuminate\Http\JsonResponse
    {
        [$region, $solId] = getSolRegion();
        $tranDate = $request->tranDate;
        $tranType = $request->terminal;
        $settledQuery = SettlementEntry::query();
        if(!empty($region)){
            $settledQuery = $settledQuery->where('Region', $region);
        }

        if(!empty($solId)){
            $settledQuery = $settledQuery->where('SolId', $solId);
        }

        if(!empty($tranDate)){
            $settledQuery = $settledQuery->whereDate('EntryDate', $tranDate);
        }

        if(!empty($tranType)){
            $settledQuery = $settledQuery->where('TerminalType', $tranType);
        }

        $settled = $settledQuery->paginate(15);

        return response()->json($settled);
    }

    public function reversed (Request $request): \Illuminate\Http\JsonResponse
    {
        [$region, $solId] = getSolRegion();
        $tranDate = $request->tranDate;
        $tranType = $request->terminal;
        $settledQuery = ReversedEntry::query();
        if(!empty($region)){
            $settledQuery = $settledQuery->where('Region', $region);
        }

        if(!empty($solId)){
            $settledQuery = $settledQuery->where('SolId', $solId);
        }

        if(!empty($tranDate)){
            $settledQuery = $settledQuery->whereDate('DateLocal', $tranDate);
        }

        if(!empty($tranType)){
            $settledQuery = $settledQuery->where('TranType', $tranType);
        }

        $settled = $settledQuery->paginate(15);

        return response()->json($settled);
    }

    public function unImpacted (Request $request): \Illuminate\Http\JsonResponse
    {
        [$region, $solId] = getSolRegion();
        $tranDate = $request->tranDate;
        $tranType = $request->terminal;
        $settledQuery = UnImpactedEntry::query();
        if(!empty($region)){
            $settledQuery = $settledQuery->where('Region', $region);
        }

        if(!empty($solId)){
            $settledQuery = $settledQuery->where('SolId', $solId);
        }

        if(!empty($tranDate)){
            $settledQuery = $settledQuery->whereDate('DateLocal', $tranDate);
        }

        if(!empty($tranType)){
            $settledQuery = $settledQuery->where('TranType', $tranType);
        }

        $settled = $settledQuery->paginate(15);

        return response()->json($settled);
    }



}
