<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_Model_User_Cookie extends ORM {

    protected $_table_name = 'user_cookies';

    protected $_primary_key = 'cookie_id';

    protected $_table_columns = array(
        'cookie_id' => array(),
        'user_id' => array(),
        'random_key' => array(),
        'valid_until' => array()
    );


    protected $_belongs_to = array(
        'user' => array(
            'model'   => 'user',
        ),
    );

    /**
     * Defines validation rules
     *
     * @return  array
     */
    public function rules()
    {
        return array(
            'user_id' => array(
                array('not_empty')
            ),
            'random_key' => array(
                array('not_empty')
            ),
        );
    }
}

// END Kohana_Model_User_Cookie