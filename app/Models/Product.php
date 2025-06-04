<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products'; // تأكد من أن هذا هو اسم الجدول في قاعدة البيانات

    protected $fillable = ['id','Name',
    'Discription',
    'Price','image','catigory','discription2', 'CreatedBy','status','offer_price','offer_price'];
    protected $primaryKey = 'id';
    public function creator()
{
    return $this->belongsTo(Account::class, 'CreatedBy', 'Account_id');
}
public function cartItems() {
    return $this->hasMany(ShoppingCartItem::class, 'Product_id', 'id'); // بدل 'id' مكان 'Product_id'
}
public function orderItems() {
    return $this->hasMany(OrderItem::class, 'Product_id', 'id'); // كذلك هنا
}
    public function product()
    {
        return $this->hasMany(Product::class, 'product_id');
    }
}

