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
        'valid_until' => array()
    );

    /**
     * Define validation rules
     *
     * @return  array
     */
    public function rules()
    {
        return array(
            'secure_key' => array(
                array('not_empty')
            ),
            'email' => array(
                array('not_empty'),
                array('email')
            ),
        );
    }

    /**
     * Generate a new password recovery link
     *
     * @param   string  $email
     * @return  Model_Password_Recovery_Link
     */
    public function generate($email)
    {
        $config = Kohana::$config->load('auth/recovery');
        $this->secure_key = Text::random('alnum', 32);
        $this->email = $email;
        $this->valid_until = date('Y-m-d H:i:s', time() + $config['link']['lifetime']);
        return $this;
    }

    /**
     * Return secure_key
     *
     * @return  string
     */
    public function secure_key()
    {
        return $this->secure_key;
    }

    /**
     * Return the email address associated with a secure key, or throw exception
     *
     * @param   string  $secure_key
     * @throws  Password_Recovery_Link_Exception
     * @return  string
     */
    public function get_email($secure_key)
    {
        $link = ORM::factory('Password_Recovery_Link')
                    ->where('secure_key', '=', $secure_key)
                    ->and_where('valid_until', '>', date('Y-m-d H:i:s'))
                    ->find();

        if ( ! $link->loaded())
        {
            throw new Password_Recovery_Link_Exception(Kohana::message('auth/'.i18n::lang().'/auth', 'reset.invalid_secure_key'));
        }

        return $link->email;
    }
}

// END Kohana_Model_Password_Recovery_Link