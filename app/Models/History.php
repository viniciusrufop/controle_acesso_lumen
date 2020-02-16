<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = [
        'data_user_id',
        'data',
        'hora',
        'tag_value'
    ];

    public function dataUser()
    {
        return $this->belongsTo(DataUser::class,'data_user_id','id');
    }
}
