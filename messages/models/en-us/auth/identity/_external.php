<?php defined('SYSPATH') or die('No direct script access.');
return
array(
    'password' => array(
        'not_empty'      => 'You can&#39;t leave password empty.',
        'min_length'     => 'Password must be at least 6 characters long.',
        'max_length'     => 'Password must be at most 16 characters long.',
        'not_numeric'    => 'Your password can&#39;t contain only digits.',
    ),
    'password_confirm' => array(
        'matches'   => 'These passwords don&#39;t match.',
    ),
);

