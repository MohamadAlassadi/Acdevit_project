<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTransaction extends Model
{
    protected $fillable = ['batch_id', 'type', 'quantity', 'transaction_date', 'party', 'notes','totalPrice','price'];

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }
}
