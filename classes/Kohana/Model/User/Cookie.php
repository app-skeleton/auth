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
        'secure_key' => array(),
        'expires_on' => array()
    );


    protected $_belongs_to = array(
        'user' => array(
            'model'   => 'user'
        ),
    );

    /**
     * Garbage collector
     *
     * @param   int     $start_time
     */
    public function garbage_collector($start_time)
    {
        // Delete outdated cookies
        DB::delete('auth_cookies')
            ->where('expires_on', '<', date('Y-m-d H:i:s', $start_time))
            ->execute($this->_db);
    }
}

// END Kohana_Model_User_Cookie