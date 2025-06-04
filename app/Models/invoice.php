<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'driver_id',
        'order_id',
        'orderPrice',
        'deliveryPrice',
        'totalPrice',
        'payment_status',
        'payment_method',
        'discount_type',
        'discount_amount',
        'discount_status'
    ];
    
        public function Clients()
        {
            return $this->belongsTo(Account::class, 'client_id', 'Account_id');
        }
        public function Drivers()
        {
            return $this->belongsTo(Account::class, 'driver_id', 'Account_id');
        }
        public function Orders()
        {
            return $this->belongsTo(Orders::class, 'order_id', 'id');
        }
    }
