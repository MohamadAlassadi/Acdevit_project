<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCartItem extends Model
{
    use HasFactory;
    protected $primaryKey = 'ShoppingCarItem_id'; // or your actual primary key name
    public $incrementing = false; // if it's not auto-incrementing
    protected $keyType = 'string'; 
    protected $table = 'shopping_cart_items';

    protected $fillable = [
        'CartID',
        'Product_id',
        'Quantity',
        'UnitPrice',
        'TotalPrice',
    ];

    // علاقة مع جدول Cart
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'CartID', 'id');
    }
    
    // علاقة مع جدول Product (إذا كان لديك جدول منتجات)
    public function product()
    {
        return $this->belongsTo(Product::class, 'Product_id', 'id');
    }
}
