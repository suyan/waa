<?php

Route::pattern('host', '[0-9]+');
Route::pattern('code', '[0-9a-z]+');

Route::get('/', 'UserSumController@getSum');
# TODO 修改用户首页route
Route::get('about', 'WaaController@getAbout');
Route::get('demo', 'WaaController@getDemo');
Route::get('activity', 'UserActivityController@getActivity');
Route::get('home', 'UserSumController@getSum');

Route::group(array('prefix'=>'host'), function(){
    Route::get('host', 'UserHostController@getHost');
    Route::get('create', 'UserHostController@getCreate');
    Route::post('create', 'UserHostController@postCreate');
    Route::get('{host}/delete', 'UserHostController@getDelete');
    Route::post('{host}/delete', 'UserHostController@postDelete');
    Route::get('{host}/run', 'UserHostController@getRun');
    Route::post('{host}/run', 'UserHostController@postRun');
    Route::get('{host}/info', 'UserHostController@getInfo');
    Route::get('{host}/vector', 'UserHostController@getVector');
    Route::controller('', 'UserHostController');
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


