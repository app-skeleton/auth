<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(
    // Recovery email
    'email' => array(
        'subject' => 'Recover you password',
        'sender' => array(
            'email' => 'noreply@appdomain.com',
            'name'  => APPNAME
        )
    ),

    // Recovery link
    'link' => array(
        'lifetime' => 3600 * 24
    )
);
