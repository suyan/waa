<?php

class SettingsTableSeeder extends Seeder {

	public function run()
	{
		DB::table('settings')->delete();
        $settings = array(
            array(
                'name'         => 'waa.paginate',
                'display_name' => 'paginate',
                'value'        => '10',
                'description'  => 'the page count of app',
                'confirmed'    => 1,
                'created_at'   => date('Y-m-d H:i:s', time()),
                'updated_at'   => date('Y-m-d H:i:s', time()),
            ),
        );

        DB::table('settings')->insert( $settings );
	}

}