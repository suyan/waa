<?php

class UserController extends BaseController {

    public function __construct(){
        parent::__construct();
        $this->topNav = array(
            'home'  => array(
                'name' => 'home.home', 
                'url' => '/', 
                'class' => ''),
            'about' => array(
                'name' => 'home.about', 
                'url' => 'about', 
                'class' => ''),
        );
        $this->topNav['about']['class'] = 'active';
        View::share('topNav', $this->topNav);
    }

    public function getCreate(){
        return View::make('pages.create')
            ->with('title',Lang::get('user.sign_up'));
    }

    public function postCreate(){
        $user = new User;
        $user->username = Input::get( 'username' );
        $user->email    = Input::get( 'email' );
        $user->password = Input::get( 'password' );
        $user->password_confirmation = Input::get( 'password1' );
        $user->save();

        if ( $user->id ){
            return Redirect::to('user/login')
                ->with('info', Lang::get('create_success'));
        } else {
            $errors = $user->errors();
            return Redirect::to('user/create')
                ->withInput(Input::except('password'))
                ->with( 'errors', $errors );
        }
    }

    public function getLogin(){
        if (Confide::user()) {
            return Redirect::to('/');
        } else {
            return View::make('pages.login')
                ->with('title',Lang::get('user.sign_in'));
        }
    }

    public function postLogin() { 
        $input = array(
            'email'    => Input::get( 'email' ), 
            'password' => Input::get( 'password' ),
            'remember' => Input::get( 'remember' ),
        );

        if (Confide::logAttempt($input, Config::get('confide::signup_confirm'))) {
            Event::fire('user.login', array(Auth::user()->id));
            if(Auth::user()->hasRole('admin'))
                return Redirect::to('admin');     
            else
                return Redirect::intended('/'); 
        } else {
            $user = new User;
            if(Confide::isThrottled( $input )) {
                $err_msg = Lang::get('user.too_many_attempts');
            } elseif ($user->checkUserExists($input) and !$user->isConfirmed($input)) {
                $err_msg = Lang::get('user.not_confirmed');
            } else {
                $err_msg = Lang::get('user.wrong_credentials');
            }
            return Redirect::to('user/login')
                ->withInput(Input::except('password'))
                ->with('error', $err_msg);
        }
    }

    public function getConfirm( $code ){
        if (Confide::confirm($code)) {
            $notice_msg = Lang::get('user.confirmation');
            return Redirect::to('user/login')
                ->with('notice', $notice_msg );
        } else {
            $error_msg = Lang::get('user.wrong_confirmation');
            return Redirect::to('user/login')
                ->with( 'error', $error_msg );
        }
    }

    public function getLogout(){
        Confide::logout();
        return Redirect::to('/');
    }

/*
    public function getForgotPassword(){
        return View::make(Config::get('confide::forgot_password_form'));
    }

    public function postForgotPassword(){
        if( Confide::forgotPassword( Input::get( 'email' ) ) ){
            $notice_msg = Lang::get('confide::confide.alerts.password_forgot');
            return Redirect::action('UserController@login')
                ->with( 'notice', $notice_msg );
        } else {
            $error_msg = Lang::get('confide::confide.alerts.wrong_password_forgot');
            return Redirect::action('UserController@forgot_password')
                ->withInput()
                ->with( 'error', $error_msg );
        }
    }

    public function getResetPassword( $token ){
        return View::make(Config::get('confide::reset_password_form'))
                ->with('token', $token);
    }

    public function postResetPassword(){
        $input = array(
            'token'=>Input::get( 'token' ),
            'password'=>Input::get( 'password' ),
            'password_confirmation'=>Input::get( 'password_confirmation' ),
        );

        // By passing an array with the token, password and confirmation
        if( Confide::resetPassword( $input ) )
        {
            $notice_msg = Lang::get('confide::confide.alerts.password_reset');
                        return Redirect::action('UserController@login')
                            ->with( 'notice', $notice_msg );
        }
        else
        {
            $error_msg = Lang::get('confide::confide.alerts.wrong_password_reset');
                        return Redirect::action('UserController@reset_password', array('token'=>$input['token']))
                            ->withInput()
                ->with( 'error', $error_msg );
        }
    }
*/
}
