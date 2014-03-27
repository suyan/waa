<?php

Route::pattern('host', '[0-9]+');
Route::pattern('code', '[0-9a-z]+');

Route::get('/', 'UserSumController@getSum');
Route::get('about', 'WaaController@getAbout');
Route::get('demo', 'WaaController@getDemo');
Route::get('activity', 'UserActivityController@getActivity');

Route::group(array('prefix'=>'host'), function(){
    Route::get('host', 'UserHostController@getHost');
    Route::get('create', 'UserHostController@getCreate');
    Route::post('create', 'UserHostController@postCreate');
    Route::get('{host}/delete', 'UserHostController@getDelete');
    Route::post('{host}/delete', 'UserHostController@postDelete');
    Route::get('{host}/run', 'UserHostController@getRun');
    Route::post('{host}/run', 'UserHostController@postRun');
    Route::get('{host}/info', 'UserHostController@getInfo');
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
    Route::get('task', 'AdminTaskController@getWait');
    Route::get('task/run', 'AdminTaskController@getRun');
    Route::get('task/done', 'AdminTaskController@getDone');
    Route::get('user', 'AdminUserController@getUser');
    Route::get('role', 'AdminUserController@getRole');
    Route::get('permission', 'AdminUserController@getPermission');
});


