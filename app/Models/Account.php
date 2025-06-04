<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Account extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'accounts'; // مهم جداً

    protected $primaryKey = 'Account_id'; // اسم الـ Primary Key

    protected $fillable = [
        'User_Name',
        'Email',
        'Password',
        'Address',
        'Phone_Number',
        'Ststus',
        'First_Name',
        'Last_Name',
        'D_Experince_years',
        'D_Partial_certificate',
        'Birth_date',
        'Role_id',
        'CreatedBy',
        'image',
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    // Laravel يتوقع هذا الاسم الافتراضي لكلمة المرور، فلازم تحدده
    public function getAuthPassword()
    {
        return $this->Password;
    }
    public function products()
{
    return $this->hasMany(Product::class, 'CreatedBy', 'Account_id');
}
public function articles()
{
    return $this->hasMany(Article::class, 'CreatedBy', 'Account_id');
}
public function carts()
{
    // ربط حساب واحد مع العديد من السلال باستخدام Client_id
    return $this->hasMany(Cart::class, 'Client_id', 'Account_id');
}
public function orders()
    {
        // ربط حساب واحد بالعديد من الطلبات باستخدام Client_id
        return $this->hasMany(Order::class, 'Client_id', 'Account_id');
    }
    public function doctorConsultations()
    {
        return $this->hasMany(Consultation::class, 'Doctor_id', 'Account_id');
    }

    // علاقة مع الاستشارات كعميل
    public function clientConsultations()
    {
        return $this->hasMany(Consultation::class, 'Client_id', 'Account_id');
        
        return $this->hasMany(ConsultationReply::class, 'doctor_id', 'Account_id');


    }
    public function clientConsultationsReplay()
    {
        return $this->hasMany(ConsultationReply::class, 'Client_id', 'Account_id');
    }
    public function doctorConsultationsReplay()
    {
        return $this->hasMany(ConsultationReply::class, 'doctor_id', 'Account_id');
    }
    public function Drivers()
    {
        return $this->belongsTo(Delivery::class, 'driver_id', 'Account_id');
    }
    public function Clients()
    {
        return $this->belongsTo(Delivery::class, 'client_id', 'Account_id');
    }
    public function Clientsi()
    {
        return $this->belongsTo(invoice::class, 'client_id', 'Account_id');
    }
    public function Driversi()
    {
        return $this->belongsTo(invoice::class, 'driver_id', 'Account_id');
    }
        public function source()
    {
        return $this->belongsTo(notifications::class, 'source_id', 'Account_id');
    }
        public function dest()
    {
        return $this->belongsTo(notifications::class, 'dest_id', 'Account_id');
    }
       public function source1()
    {
        return $this->belongsTo(copon::class, 'source_id', 'Account_id');
    }
        public function dest1()
    {
        return $this->belongsTo(copon::class, 'dest_id', 'Account_id');
    }
}
