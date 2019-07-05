<?php

namespace App\Repositories;

use App\Models\User;

use Illuminate\Support\Facades\Hash;

class UserRepositoryEloquent implements UserRepositoryInterface
{

   private $model;

   public function __construct(User $user)
   {
      $this->model = $user;
   }

   public function get($id)
   {
      return $this->model->find($id);
   }

   public function login($user)
   {
      $checkUser = $this->model
                     ->where('email',$user['email'])
                     ->first();
      if($checkUser && Hash::check($user['password'], $checkUser['password']))
         return true;

      return false;
   }

   
}