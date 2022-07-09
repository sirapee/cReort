<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettlementEntry extends Model
{
    use HasFactory;

    protected $fillable  = [
        'NarrationFinacle', 'AccountNameFinacle', 'AccountNumberFinacle', 'TranCurrencyFinacle', 'TranIdFinacle','TriggeredBy','PanFinacle',
        'AmountFinacle', 'StanFinacle', 'RetrievalReferenceNumberFinacle', 'TerminalIdFinacle', 'Status', 'BatchNumber', 'SolId',  'Region',
        'ValueDateFinacle','TranDateFinacle', 'EntryDate', 'TerminalType'

    ];
}
