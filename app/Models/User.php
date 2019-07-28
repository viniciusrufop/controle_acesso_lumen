<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    
    protected $table = 'users';

    protected $fillable = [
        'email',
        'password',
    ];

    public $timestamps = true;
    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function dataUser()
    {
        return $this->hasOne(DataUser::class,'user_id','id');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class,'user_id','id');
    }
}
