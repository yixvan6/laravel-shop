<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserAddress;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class, 60)->create()->each(function (User $user) {
            $user->addresses()->saveMany(factory(UserAddress::class, random_int(1, 3)));
        });
    }
}
