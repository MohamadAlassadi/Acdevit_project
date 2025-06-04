<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'source_id', 'dest_id', 'type',
        'discount_amount', 'discount_percent',
        'status', 'payment_status',
        'content', 'expiry_date','code'
    ];

    public function sourceAccount() {
        return $this->belongsTo(Account::class, 'source_id', 'Account_id');
    }

    public function destAccount() {
        return $this->belongsTo(Account::class, 'dest_id', 'Account_id');
    }
    public function paymentDiscount()
{
    return $this->belongsTo(PaymentDiscount::class);
}

}
