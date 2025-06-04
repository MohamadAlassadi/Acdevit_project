<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    // تحديد اسم الجدول
    public $incrementing = true;
    protected $keyType = 'int';
    
    // الحقول القابلة للتعبئة
    protected $fillable = [
        'Client_id', 
        'Date_added', 
        'IsCheckedOut',
    ];

    // تحديد الحقول التي يجب تحويلها إلى تواريخ
    protected $dates = [
        'Date_added',
    ];

    // تحديد كيفية التعامل مع القيم البوليانية
    protected $casts = [
        'IsCheckedOut' => 'boolean',
    ];

    // علاقة مع حساب العميل
    public function account()
    {
        return $this->belongsTo(Account::class, 'Client_id', 'Account_id');
    }

    // علاقة "واحدة إلى العديد" مع الطلبات
    public function orders()
    {
        return $this->hasMany(Order::class, 'Cart_id', 'id');
    }

    // علاقة "واحدة إلى العديد" مع عناصر السلة
    public function items()
    {
        return $this->hasMany(ShoppingCartItem::class, 'CartID', 'id');
    }
    

    // دالة للحصول على عدد العناصر في السلة
    public function getItemCountAttribute()
    {
        return $this->items->count();
    }

    // دالة لحساب إجمالي سعر السلة
    public function getTotalPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->Quantity * $item->UnitPrice;
        });
    }
}
