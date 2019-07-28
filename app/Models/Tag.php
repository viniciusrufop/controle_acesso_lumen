<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{

    protected $fillable = [
        'tag_value',
        'data_user_id',
        'ativo',
    ];

    public function dataUser()
    {
        return $this->belongsTo(DataUser::class,'data_user_id','id');
    }
}
