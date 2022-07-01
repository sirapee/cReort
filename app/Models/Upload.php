<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected static $usersModel = 'Cartalyst\Sentinel\Users\EloquentUser';

    /**
     * {@inheritDoc}
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(static::$usersModel);
    }
}
