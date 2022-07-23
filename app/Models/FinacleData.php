<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinacleData extends Model
{
    use HasFactory;

    protected $fillable  = [
        'NarrationFinacle', 'AccountNameFinacle', 'AccountNumberFinacle', 'TranCurrencyFinacle', 'TranIdFinacle','TriggeredBy','PanFinacle',
        'AmountFinacle', 'StanFinacle', 'RetrievalReferenceNumberFinacle', 'TerminalIdFinacle', 'Status', 'BatchNumber', 'SolId',  'Region',
        'ValueDateFinacle','TranDateFinacle', 'EntryDate', 'TerminalType'

    ];
}
