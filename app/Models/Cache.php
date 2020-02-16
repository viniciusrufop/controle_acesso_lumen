<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cache extends Model
{
    protected $table = 'caches';

    protected $fillable = ['options'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
