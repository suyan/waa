<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class RolesTableSeeder extends Seeder {

	public function run()
	{
		DB::table('roles')->delete();

		$adminRole = new Role;
		$adminRole->name = 'admin';
		$adminRole->save();

		$userRole = new Role;
		$userRole->name = 'user';
		$userRole->save();

		$user = User::where('username','=','admin')->first();
		$user->attachRole( $adminRole );

		$user = User::where('username','=','user')->first();
		$user->attachRole( $userRole );
	}

}