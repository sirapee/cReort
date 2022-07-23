<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NibssPostingEntry extends Model
{
    use HasFactory;

    protected $table = 'nibss_posting_entries';

    protected $fillable = [
        'SessionId', 'Amount', 'TransactionTime','SourceInstitution', 'SenderName',
        'Destination', 'DestinationAccountName', 'EntryDate', 'Narration','RequestedBy','PostingAmount',
        'DestinationAccountNumber','DebitAccountNumber', 'CreditAccountNumber','Amount', 'TranType', 'BatchNumber', 'ThreadId'
    ];
}
