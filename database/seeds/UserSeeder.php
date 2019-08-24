<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User;
        $user->email = 'adminuser@admin.com';
        $user->password = Hash::make('!Admin123'.$i);
        $user->save();
        // for ($i=0; $i < 5; $i++) { 
        // 	$user = new User;
        // 	$user->email = 'email'.$i.'@gmail.com';
        // 	$user->password = Hash::make('12345'.$i);
        // 	$user->save();
        // }
    }
}
