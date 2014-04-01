<?php

class SettingsTableSeeder extends Seeder {

	public function run()
	{
		DB::table('settings')->delete();
        $settings = array(
            array(
                'group'        => 'waa',
                'name'         => 'paginate',
                'display_name' => 'paginate',
                'value'        => json_encode(10),
                'description'  => 'the page count of app',
                'confirmed'    => 1,
                'created_at'   => date('Y-m-d H:i:s', time()),
                'updated_at'   => date('Y-m-d H:i:s', time()),
            ),
            array(
                'group'        => 'lorg',
                'name'         => 'tamper.tamperTest',
                'display_name' => 'tamperTest',
                'value'        => json_encode(false),
                'description'  => 'do the tamper test in detection',
                'confirmed'    => 1,
                'created_at'   => date('Y-m-d H:i:s', time()),
                'updated_at'   => date('Y-m-d H:i:s', time()),
            ),
        );

        DB::table('settings')->insert( $settings );
	}

}