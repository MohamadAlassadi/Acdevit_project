<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Order;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'client_id',
        'order_id',
        'adress',
        'status',
        'expected_hours',
    ];
    public function Clients()
    {
        return $this->belongsTo(Account::class, 'client_id', 'Account_id');
    }
    public function Drivers()
    {
        return $this->belongsTo(Account::class, 'driver_id', 'Account_id');
    }
    public function orders()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }
}
