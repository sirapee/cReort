<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllReconData extends Model
{
    use HasFactory;

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
        'TriggeredBy'

    ];
}
