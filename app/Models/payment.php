<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payment extends Model
{
    protected $table = 'payment';

    protected $fillable = [
        'user_id', 'bank_name','method', 'credit_number', 'syriatel_cash',
        'cvv', 'expiry_date', 'balance'
    ];
}

