<?php defined('SYSPATH') or die('No direct script access.');
return
array(
    'username'  => array(
        'not_empty'             => 'Provide a username.',
        'min_length'            => 'Username must be at least 4 characters long.',
        'max_length'            => 'Username must be at most 32 characters long.',
        'regex'                 => 'Username must contain only letters, numbers and periods.',
        'username_available'    => 'This username is already registered.',
        'not_reserved'          => 'This username is reserved.',
    ),

    'email' => array(
        'not_empty'         => 'Provide your email address.',
        'email'             => 'Please provide a valid email address.',
        'email_available'   => 'This email address is already registered.',
    ),

    '_external' => array(
        'password' => array(
            'not_empty'      => 'Choose a password.',
            'min_length'     => 'Password must be at least 6 characters long.',
            'max_length'     => 'Password must be at most 32 characters long.',
            'not_numeric'    => 'Your password can&#39;t contain only digits.',
        ),
        'password_confirm' => array(
            'matches'   => 'These passwords don&#39;t match.',
        ),
    )
);

