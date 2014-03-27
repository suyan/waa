<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder {

    public function run()
    {
        DB::table('users')->delete();
        $users = array(
            array(
                'username'          => 'admin',
                'email'             => 'admin@admin.com',
                'password'          => Hash::make('123qwe'),
                'confirmed'         => 1,
                'confirmation_code' => md5(microtime().Config::get('app.key')),
                'created_at'        => new DateTime,
                'updated_at'        => new DateTime,
            ),
            array(
                'username'          => 'user',
                'email'             => 'user@user.com',
                'password'          => Hash::make('123qwe'),
                'confirmed'         => 1,
                'confirmation_code' => md5(microtime().Config::get('app.key')),
                'created_at'        => new DateTime,
                'updated_at'        => new DateTime,
            )
        );

        DB::table('users')->insert( $users );
    }

}