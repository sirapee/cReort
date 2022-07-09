<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettledEntry extends Model
{
    use HasFactory;

    protected $fillable  = [
        'NarrationFinacle', 'AccountNameFinacle', 'AccountNumberFinacle', 'TranCurrencyFinacle', 'TranIdFinacle','TriggeredBy',
        'AmountFinacle', 'StanFinacle', 'RetrievalReferenceNumberFinacle', 'TerminalIdFinacle', 'Status', 'BatchNumber', 'SolId'
    ];
}
