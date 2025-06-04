<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class complains extends Model
{
 protected  $fillable=['source_id','dest_id','content'];
}
