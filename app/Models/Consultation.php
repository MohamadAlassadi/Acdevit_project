<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $table = 'consultations';

    protected $fillable = [
        'Doctor_id',
        'Client_id',
        'Consulation_date', // تأكد من أن الاسم مطابق للجدول (بدون 't')
        'type',
        'age',
        'weight',
        'prev_illness',
        'description',
        'Follow_update',
        'doctor_replay',
        'image'
    ];

    // علاقة مع حساب الطبيب
    public function doctor()
    {
        return $this->belongsTo(Account::class, 'Doctor_id', 'Account_id');
    }

    // علاقة مع حساب العميل
    public function client()
    {
        return $this->belongsTo(Account::class, 'Client_id', 'Account_id');

    }
    public function replies()
    {
        return $this->belongsTo(Consultation::class, 'Consulation_id');
    }
    
}