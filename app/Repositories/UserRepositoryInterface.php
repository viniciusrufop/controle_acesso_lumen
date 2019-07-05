<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
   public function __construct(User $user);

	//public function getAll();
	
	public function get($id);

	public function login($user);
	
	/* public function store(array $data);
	
	public function update($id, array $data);
	
	public function destroy($id); */
}