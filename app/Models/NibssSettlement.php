<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NibssSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'Channel', 'SessionId', 'TransactionType', 'Response', 'Amount', 'TransactionTime', 'SourceInstitution',
        'SenderName', 'DestinationBank', 'DestinationAccountName', 'DestinationAccountNumber',
        'Narration', 'paymentReference', 'Direction', 'BatchNumber', 'Status', 'RequestDate', 'RequestedBy'
    ];
}
