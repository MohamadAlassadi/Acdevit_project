<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockLevel extends Model
{
    use HasFactory;

    protected $fillable = ['batch_id', 'current_quantity'];

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            ProductBatch::class,
            'id',           // foreign key on ProductBatch
            'id',           // foreign key on Product
            'batch_id',     // local key on StockLevel
            'product_id'    // local key on ProductBatch
        );
    }
}
