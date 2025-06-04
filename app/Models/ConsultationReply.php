<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


    class ConsultationReply extends Model
    {
        protected $fillable = ['Consulation_id', 'doctor_id','client_id', 'reply_text', 'attachment'];
        public function consultation()
        {
            return $this->belongsTo(Consultation::class, 'Consulation_id');
        }
        
        public function doctor()
        {
            return $this->belongsTo(Account::class, 'doctor_id', 'Account_id');
        }
        
        public function client()
        {
            return $this->hasmany(Account::class, 'client_id', 'Account_id');
        }
        
    }
    
