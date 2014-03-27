<?php
App::before(function($request){});
App::after(function($request, $response){});


Route::filter('auth', function()
{
	if (Auth::guest()) return Redirect::guest('user/login');
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

Route::filter('auth.admin', function(){
    if (Auth::guest()) return Redirect::guest('user/login');
    if (!Auth::user()->hasRole('admin')) 
        return Redirect::to('/')
            ->with('error', Lang::get('admin.not_admin'));
});


Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});