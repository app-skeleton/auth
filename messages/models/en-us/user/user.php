<?php defined('SYSPATH') or die('No direct script access.');
return
array(
    'first_name'  => array(
        'not_empty' => 'Enter your first name.'
    ),

    'full_name'  => array(
        'not_empty' => 'Enter your full name.'
    ),

    'timezone'  => array(
        'valid_timezone' => 'Please select a timezone.'
    )
);
?>
