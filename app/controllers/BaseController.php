<?php
class BaseController extends Controller {

    public function __construct()
    {
        $this->beforeFilter('csrf', array('on' => 'post'));
    }

    public function missingMethod($parameters = array())
    {
        return 'not found';
    }
       
}