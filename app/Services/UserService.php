<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

//use App\Models\ValidationCar;
use App\Repositories\UserRepositoryInterface;
use App\Exceptions\CustomValidationException;

class UserService
{
   private $repo;

   public function __construct(UserRepositoryInterface $repo)
   {
      $this->repo = $repo;
   }

   public function get($id)
   {
      return $this->repo->get($id);
   }

   public function login($req)
   {
      return $this->repo->login($req);
   }
}