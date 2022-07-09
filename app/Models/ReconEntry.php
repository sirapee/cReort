<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconEntry extends Model
{
    use HasFactory;

    public $table = 'reconciled_entries';
    protected $fillable = [
        'BatchNumber',
        'Status',
        'Region',
        'Pan',
        'AccountNumber',
        'RetrievalReferenceNumberPostilion',
        'StanPostilion',
        'AmountPostilion',
        'TranType',
        'DateLocal',
        'TerminalIdPostilion',
        'MessageType',
        'IssuerName',
        'RequestDate',
        'ResponseCode',
        'SolId',
        'TriggeredBy',
        'NarrationFinacle', 'AccountNameFinacle', 'AccountNumberFinacle', 'TranCurrencyFinacle', 'TranIdFinacle',
        'AmountFinacle', 'StanFinacle', 'RetrievalReferenceNumberFinacle', 'TerminalIdFinacle'

    ];
}
