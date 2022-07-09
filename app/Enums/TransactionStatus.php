<?php

namespace App\Enums;

enum TransactionStatus : string
{
    case RECONCILED = 'Reconciled';
    case SUCCESSFUL = 'Successful';
    case UNIMPACTED = 'UnImpacted';
    case REVERSED = 'Reversed';
    case SETTLEMENT = 'Settlement';
}
