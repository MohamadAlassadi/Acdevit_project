<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = ['product_id', 'batch_number', 'manufacture_date', 'expiry_date', 'quantity','totalPrice','Price'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function transactions()
    {
        return $this->hasMany(ProductTransaction::class, 'batch_id');
    }

    // العلاقة بين الدفعة والمخزون
    public function stockLevel()
    {
        return $this->hasOne(StockLevel::class, 'batch_id', 'id');
    }
}
