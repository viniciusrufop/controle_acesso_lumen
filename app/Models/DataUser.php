<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataUser extends Model
{

    protected $fillable = [
        'user_id',
        'nome',
        'sobrenome',
        'telefone',
        'cep',
        'logradouro',
        'bairro',
        'complemento',
        'cidade',
        'estado',
        'login',
        'senha',
        'ativo',
    ];

    protected $hidden = ['senha']; 

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function tags()
    {
        return $this->hasMany(Tag::class,'data_user_id','id');
    }

    public function histories()
    {
        return $this->hasMany(History::class,'data_user_id','id');
    }

    public function adjustmentRequest()
    {
        return $this->hasMany(AdjustmentRequest::class,'data_user_id','id');
    }
}
