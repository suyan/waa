<?php
class Host extends \LaravelBook\Ardent\Ardent
{
    
    public static $rules = array(
        'hostname' => 'required|alpha_dash|between:4,20',
        'domain' => 'required|between:2,50', //TODO
        'description' => 'required|between:2,100',
    );
    
    public function user()
    {
        return $this->belongsTo('User');
    }
}
