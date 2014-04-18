<?php

Route::pattern('host', '[0-9]+');
Route::pattern('code', '[0-9a-z]+');

Route::get('/', 'WaaController@getIndex');
Route::get('about', 'WaaController@getAbout');
Route::get('demo', 'WaaController@getDemo');


Route::group(array('prefix'=>'home'), function(){
    Route::get('/', 'HomeSumController@getSum');
    Route::get('activity', 'HomeActivityController@getActivity');

    Route::get('host/host', 'HomeHostController@getHost');
    Route::get('host/{ids}/host', 'HomeHostController@getHostByIds');
    Route::get('host/create', 'HomeHostController@getCreate');
    Route::post('host/create', 'HomeHostController@postCreate');
    Route::get('host/{host}/delete', 'HomeHostController@getDelete');
    Route::post('host/{host}/delete', 'HomeHostController@postDelete');
    Route::get('host/{host}/run', 'HomeHostController@getRun');
    Route::post('host/{host}/run', 'HomeHostController@postRun');
    Route::get('host/{host}/info', 'HomeHostController@getInfo');
    Route::get('host/{host}/vector', 'HomeHostController@getVector');
    Route::controller('', 'HomeHostController');
});

// user route
Route::group(array('prefix'=>'user'), function(){
    Route::get('login', 'UserController@getLogin');
    Route::post('login', 'UserController@postLogin');
    Route::get('confirm/{code}', 'UserController@getConfirm');

    Route::get('create', 'UserController@getCreate');
    Route::post('create', 'UserController@postCreate');
    Route::get('logout', 'UserController@getLogout');
    Route::controller('', 'UserController');
    // Confide routes
    // Route::get( 'user/forgot_password',        'UserController@forgotPassword');
    // Route::post('user/forgot_password',        'UserController@doForgotPassword');
    // Route::get( 'user/reset_password/{token}', 'UserController@resetPassword');
    // Route::post('user/reset_password',         'UserController@doResetPassword');
});

Route::group(array('prefix'=>'admin'), function(){
    Route::get('/', 'AdminSumController@getSum');
    Route::get('host', 'AdminHostController@getHost');
    Route::get('host/{ids}/host', 'AdminHostController@getHostByIds');
    Route::get('host/{host}/delete', 'AdminHostController@getDelete');
    Route::post('host/{host}/delete', 'AdminHostController@postDelete');
    Route::get('host/{host}/run', 'AdminHostController@getRun');
    Route::post('host/{host}/run', 'AdminHostController@postRun');
    Route::get('host/{host}/info', 'AdminHostController@getInfo');
    Route::get('host/{host}/vector', 'AdminHostController@getVector');


    Route::get('task', 'AdminTaskController@getProcess');
    Route::get('task/wait', 'AdminTaskController@getWait');
    Route::get('task/run', 'AdminTaskController@getRun');
    Route::get('task/done', 'AdminTaskController@getDone');
    Route::get('task/process', 'AdminTaskController@getProcess');
    Route::get('task/{process}/start', 'AdminTaskController@getProcessStart');
    Route::post('task/{process}/start', 'AdminTaskController@postProcessStart');
    Route::get('task/{process}/stop', 'AdminTaskController@getProcessStop');
    Route::post('task/{process}/stop', 'AdminTaskController@postProcessStop');

    Route::get('user', 'AdminUserController@getUser');
    Route::get('role', 'AdminUserController@getRole');
    Route::get('permission', 'AdminUserController@getPermission');

    Route::get('setting', 'AdminSettingController@getSites');
    Route::get('setting/sites', 'AdminSettingController@getSites');
    Route::get('setting/lorgs', 'AdminSettingController@getLorgs');
    Route::get('setting/regexes', 'AdminSettingController@getRegexes');
});


