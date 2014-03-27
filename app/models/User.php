<?php

use Zizaco\Confide\ConfideUser;
use Zizaco\Entrust\HasRole;

class User extends ConfideUser {
    use HasRole;
    public static $rules = array(
        'username' => 'required|alpha_num|between:6,20',
        'email' => 'required|email|unique:users',
        'password' => 'required|alpha_num|between:6,20|confirmed',
        'password_confirmation' => 'required|alpha_num|between:6,20',
    );

    public function hosts()
    {
        return $this->hasMany('Host');
    }
}