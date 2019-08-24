<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdjustmentRequest extends Model
{
    protected $fillable = [
        'data_user_id',
        'data',
        'hora',
        'atendido',
        'justificativa',
        'aceito',
    ];

    public function dataUser()
    {
        return $this->belongsTo(DataUser::class,'data_user_id','id');
    }
}
