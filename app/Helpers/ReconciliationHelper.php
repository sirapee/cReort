<?php

namespace App\Http\Controllers\API;

use App\Models\AllReconData;
use App\Models\NibssPostingEntry;
use App\Models\NibssSettlement;
use App\Models\ReconEntry;
use App\Models\ReconRequest;
use App\Models\ReversedEntry;
use App\Models\SettledEntry;
use App\Models\SettlementEntry;
use App\Models\UnImpactedEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            //Counts
            $allCount = Cache::remember('allCount', 3600, static function () use($allQuery, $solId){
                return number_format($allQuery->where('SolId', $solId)->count());
            });
            $reconciledCount = Cache::remember('reconciledCount', 3600, static function () use($reconciledQuery, $solId){
                return number_format($reconciledQuery->where('SolId', $solId)->count());
            });
            $settledCount = Cache::remember('settledCount', 3600, static function () use($settledQuery, $solId){
                return number_format($settledQuery->where('SolId', $solId)->count());
            });
            $reversedCount = Cache::remember('reversedCount', 3600, static function () use($reversedQuery, $solId){
                return number_format($reversedQuery->where('SolId', $solId)->count());
            });
            $settlementCount = Cache::remember('settlementCount', 3600, static function () use($settlementQuery, $solId){
                return number_format($settlementQuery->where('SolId', $solId)->count());
            });
            $unImpactedCount = Cache::remember('unImpactedCount', 3600, static function () use($unImpactedQuery, $solId){
                return number_format($unImpactedQuery->where('SolId', $solId)->count());
            });

            //Data
            $all = Cache::remember('all', 3600, static function () use($allGet, $solId){
                return $allGet->where('SolId', $solId)->get();
            });

            $last10Settled = Cache::remember('last10Settled', 3600, static function () use($settledQuery, $solId){
                return $settledQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->where('SolId', $solId)
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

            $last10Reversed = Cache::remember('last10Reversed', 3600, static function () use($reversedQuery, $solId){
                return $reversedQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->where('SolId', $solId)
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

            $last10Settlement = Cache::remember('last10Settlement', 3600, static function () use($settlementQuery, $solId){
                return $settlementQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, TranDateFinacle"))
                    ->where('SolId', $solId)
                    ->groupBy('BatchNumber', 'TranDateFinacle', 'status')
                    ->get();
            });

            $last10UnImpacted = Cache::remember('last10UnImpacted', 3600, static function () use($unImpactedQuery, $solId){
                return $unImpactedQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->where('SolId', $solId)
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

            $last10Reconciled = Cache::remember('last10Reconciled', 3600, static function () use($reconciledQuery, $solId){
                return $reconciledQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->where('SolId', $solId)
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });


        }elseif (!empty($region)){
            $allCount = Cache::remember('allCount', 3600, static function () use($allQuery, $region){
                return number_format($allQuery->where('Region', $region)->count());
            });
            $reconciledCount = Cache::remember('reconciledCount', 3600, static function () use($reconciledQuery, $region){
                return number_format($reconciledQuery->where('Region', $region)->count());
            });
            $settledCount = Cache::remember('settledCount', 3600, static function () use($settledQuery, $region){
                return number_format($settledQuery->where('Region', $region)->count());
            });
            $reversedCount = Cache::remember('reversedCount', 3600, static function () use($reversedQuery, $region){
                return number_format($reversedQuery->where('Region', $region)->count());
            });
            $settlementCount = Cache::remember('settlementCount', 3600, static function () use($settlementQuery, $region){
                return number_format($settlementQuery->where('Region', $region)->count());
            });
            $unImpactedCount = Cache::remember('unImpactedCount', 3600, static function () use($unImpactedQuery, $region){
                return number_format($unImpactedQuery->where('Region', $region)->count());
            });

            //Data
            $all = Cache::remember('all', 3600, static function () use($allGet, $region){
                return $allGet->where('Region', $region)->get();
            });

            $last10Settled = Cache::remember('last10Settled', 3600, static function () use($settledQuery, $region){
                return $settledQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->where('Region', $region)
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

            $last10Reversed = Cache::remember('last10Reversed', 3600, static function () use($reversedQuery, $region){
                return $reversedQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->where('Region', $region)
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

            $last10Settlement = Cache::remember('last10Settlement', 3600, static function () use($settlementQuery, $region){
                return $settlementQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, TranDateFinacle"))
                    ->where('Region', $region)
                    ->groupBy('BatchNumber', 'TranDateFinacle', 'status')
                    ->get();
            });

            $last10UnImpacted = Cache::remember('last10UnImpacted', 3600, static function () use($unImpactedQuery, $region){
                return $unImpactedQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->where('Region', $region)
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

            $last10Reconciled = Cache::remember('last10Reconciled', 3600, static function () use($reconciledQuery, $region){
                return $reconciledQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->where('Region', $region)
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

        }else{
            $allCount = Cache::remember('allCount', 3600, static function () use($allQuery){
                return number_format($allQuery->count());
            });
            $reconciledCount = Cache::remember('reconciledCount', 3600, static function () use($reconciledQuery){
                return number_format($reconciledQuery->count());
            });
            $settledCount = Cache::remember('settledCount', 3600, static function () use($settledQuery){
                return number_format($settledQuery->count());
            });
            $reversedCount = Cache::remember('reversedCount', 3600, static function () use($reversedQuery){
                return number_format($reversedQuery->count());
            });
            $settlementCount = Cache::remember('settlementCount', 3600, static function () use($settlementQuery){
                return number_format($settlementQuery->count());
            });
            $unImpactedCount = Cache::remember('unImpactedCount', 3600, static function () use($unImpactedQuery){
                return number_format($unImpactedQuery->count());
            });

            $all = Cache::remember('all', 3600, static function () use($allGet){
                return $allGet->get();
            });

            $last10Settled = Cache::remember('last10Settled', 3600, static function () use($settledQuery){
                return $settledQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

            $last10Reversed = Cache::remember('last10Reversed', 3600, static function () use($reversedQuery){
                return $reversedQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

            $last10Settlement = Cache::remember('last10Settlement', 3600, static function () use($settlementQuery){
                return $settlementQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, TranDateFinacle"))
                    ->groupBy('BatchNumber', 'TranDateFinacle', 'status')
                    ->get();
            });

            $last10UnImpacted = Cache::remember('last10UnImpacted', 3600, static function () use($unImpactedQuery){
                return $unImpactedQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

            $last10Reconciled = Cache::remember('last10Reconciled', 3600, static function () use($reconciledQuery){
                return $reconciledQuery
                    ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                    ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                    ->get();
            });

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

    public function nibssDashboard(): \Illuminate\Http\JsonResponse
    {
        //Todo based on role
        $allNibssQuery = NibssSettlement::query();

        $reversedNibssQuery = NibssPostingEntry::query();


        $allNibssGet = ReconRequest::query();
        $all = [];

        $allCount = Cache::remember('allNibssQuery', 3600, static function () use($allNibssQuery){
            return number_format($allNibssQuery->count());
        });
        $reconciledCount = Cache::remember('reconciledNibssCount', 3600, static function () use($allNibssQuery){
            return number_format($allNibssQuery->where('Status', '=', 'Reconciled')->count());
        });

        $reversedCount = Cache::remember('reversedNibssCount', 3600, static function () use($reversedNibssQuery){
            return number_format($reversedNibssQuery->where('Status', '=', 'Reversal')->count());
        });

        $unImpactedCount = Cache::remember('unImpactedNibssCount', 3600, static function () use($allNibssQuery){
            return number_format($allNibssQuery->where('Status', '=', 'UnImpacted')->count());
        });

        $all = Cache::remember('allNibss1', 3600, static function () use($allNibssGet){
            return $allNibssGet->where('Channel', 'NIP')->get();
        });

        $last10Reversed = Cache::remember('last10NibssReversed1', 3600, static function () use($reversedNibssQuery){
            return $reversedNibssQuery
                ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(EntryDate as date) as RequestDate"))
                ->where('TranType', '=', 'Reversal')
                ->groupBy(DB::raw(' status, BatchNumber, cast(EntryDate as date)'))
                ->get();
        });


        $last10UnImpacted = Cache::remember('last10NibssUnImpacted', 3600, static function () use($allNibssQuery){
            return $allNibssQuery
                ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                ->where('Status', '=', 'UnImpacted')
                ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                ->get();
        });

        $last10Reconciled = Cache::remember('last10NibssReconciled1', 3600, static function () use($allNibssQuery){
            return $allNibssQuery
                ->select(DB::raw("REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,count(*)),1), '.00','') as user_count, status, BatchNumber, cast(RequestDate as date) as RequestDate"))
                ->where('Status', '=', 'Reconciled')
                ->groupBy(DB::raw(' status, BatchNumber, cast(RequestDate as date)'))
                ->get();
        });
        $data = [
            'allRecon' => $all,
            'allProcessedCount' => $allCount,
            'reversedCount' => $reversedCount,
            'unImpactedCount' => $unImpactedCount,
            'reconciledCount' => $reconciledCount,
            'last10Reversed' => $last10Reversed,
            'last10UnImpacted' => $last10UnImpacted,
            'last10Reconciled' => $last10Reconciled
        ];


        return response()->json($data);


    }

    public function reconciledNibss (Request $request): \Illuminate\Http\JsonResponse
    {
        $tranDate = $request->tranDate;
        $batchNumber = $request->batchNumber;
        $settledQuery = NibssSettlement::query();

        if(!empty($tranDate)){
            $settledQuery = $settledQuery->whereDate('TransactionTime', $tranDate);
        }

        if(!empty($batchNumber)){
            $settledQuery = $settledQuery->where('BatchNumber', $batchNumber);
        }
        $settledQuery = $settledQuery->where('Status', '=', 'Reconciled');

        $settled = $settledQuery->paginate(15);

        return response()->json($settled);
    }

    public function reversedNibss (Request $request): \Illuminate\Http\JsonResponse
    {
        $tranDate = $request->tranDate;
        $batchNumber = $request->batchNumber;
        $settledQuery = NibssPostingEntry::query();

        if(!empty($tranDate)){
            $settledQuery = $settledQuery->where('EntryDate', $tranDate);
        }

        if(!empty($batchNumber)){
            $settledQuery = $settledQuery->where('BatchNumber', $batchNumber);
        }
        $settledQuery = $settledQuery->where('TranType', '=', 'Reversal');

        $settled = $settledQuery->paginate(15);

        return response()->json($settled);
    }



}
