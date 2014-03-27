<?php

Event::listen('user.login', function($user_id)
{
    $activity = new Activity;
    $activity->user_id = $user_id;
    $activity->message = json_encode(array('name'=>'activity.user.login'));
    $activity->save();
});