<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class notification extends Model
{
 use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'dest_id',
        'source_id',
        'Date_added',
        'type',
        'content',
    ];

    // العلاقة مع حساب العميل
    public function source()
    {
        return $this->belongsTo(Account::class, 'source_id', 'Account_id');
    }
        public function dest()
    {
        return $this->belongsTo(Account::class, 'dest_id', 'Account_id');
    }
}
