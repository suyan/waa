<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class PermissionsTableSeeder extends Seeder {

	public function run()
	{
		DB::table('permissions')->delete();

        $permissions = array(
            array(
                'name'         => 'manage_users',
                'display_name' => 'manage users',
                'created_at'   => date('Y-m-d H:i:s', time()),
                'updated_at'   => date('Y-m-d H:i:s', time()),
                ),
            array(
                'name'         => 'manage_roles',
                'display_name' => 'manage roles',
                'created_at'   => date('Y-m-d H:i:s', time()),
                'updated_at'   => date('Y-m-d H:i:s', time()),
                ),
        );

        DB::table('permissions')->insert( $permissions );

        DB::table('permission_role')->delete();

        $permissions = array(
            array('role_id' => 1, 'permission_id' => 1),
            array('role_id' => 1, 'permission_id' => 2),
        );

        DB::table('permission_role')->insert( $permissions );
	}

}