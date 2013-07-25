<?php defined('SYSPATH') or die('No direct script access.');
return
    array(
        'login' => array(
            'error' => array(
                // Wrong username
                1 => 'Wrong username or password.',
                // Wrong password
                2 => 'Wrong username or password.',
                // Empty login
                3 => 'Enter your username and password.',
                // Inactive account
                4 => 'Wrong username or password.'
            )
        ),

        'recover' => array(
            'no_email'      => 'Provide your email address.',
            'invalid_email' => 'Can\'t find an account for :email.'
        ),

        'reset' => array(
            'invalid_secure_key' => 'Invalid secure key.'
        )
    );
?>