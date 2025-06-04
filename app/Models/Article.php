<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $primaryKey = 'Article_id';
    protected $table = 'articles';

    protected $fillable = ['Title', 'Content', 'Image', 'CreatedBy','catigory'];
    public function creator()
    {
        
        return $this->belongsTo(Account::class, 'CreatedBy', 'Account_id');
    }
    }
