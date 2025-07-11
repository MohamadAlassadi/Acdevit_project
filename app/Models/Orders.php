<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'Client_id',
        'Cart_id',
        'Date_added',
        'Order_status',
        'IsCheckedOut',
    ];

    // العلاقة مع حساب العميل
    public function account()
    {
        return $this->belongsTo(Account::class, 'Client_id', 'Account_id');
    }

    // العلاقة مع السلة
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'Cart_id', 'id');
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'OrderID', 'id');
    }
    
}
