<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'OrderID',
        'Product_id',
        'Quantity',
        'UnitPrice',
        'TotalPrice',
    ];

    // علاقة مع جدول Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderID', 'id');
    }

    // علاقة مع جدول Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'Product_id', 'id');
    }
}
