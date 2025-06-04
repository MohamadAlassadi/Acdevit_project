<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'discount_percent',
        'valid_days',
        'price',
        'status',
        'createdBy',
    ];

    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'payment_discount_id');
    }
    public function creator()
{
    return $this->belongsTo(Account::class, 'createdBy');
}

}
