<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_Model_Password_Recovery_Link extends ORM {

    protected $_table_name = 'password_recovery_links';

    protected $_primary_key = 'link_id';

    protected $_table_columns = array(
        'link_id' => array(),
        'secure_key' => array(),
        'email' => array(),
        'expires_on' => array()
    );

    /**
     * Defines validation rules
     *
     * @return  array
     */
    public function rules()
    {
        return array(
            'email' => array(
                array('not_empty'),
                array('email'),
                array(array($this, 'email_exists'), array(':value')),
            )
        );
    }

    /**
     * Check if the given email exists in the database
     *
     * @param   string  $email
     * @return  bool
     */
    public function email_exists($email)
    {
        return (bool) DB::select(array(DB::expr('COUNT("*")'), 'total_count'))
            ->from('user_identities')
            ->where('email', '=', $email)
            ->where('status', '!=', Model_Identity::STATUS_INVITED)
            ->execute($this->_db)
            ->get('total_count');
    }

    /**
     * Delete all password recovery links for the given email
     *
     * @param   string  $email
     */
    public function delete_all($email)
    {
        DB::delete($this->_table_name)
            ->where('email', '=', $email)
            ->execute($this->_db);
    }

    /**
     * Garbage collector
     *
     * @param   int     $start_time
     */
    public function garbage_collector($start_time)
    {
        DB::delete('password_recovery_links')
            ->where('expires_on', '<', date('Y-m-d H:i:s', $start_time))
            ->execute($this->_db);
    }
}

// END Kohana_Model_Password_Recovery_Link
