<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_Model_Identity extends ORM {

    protected $_table_name = 'user_identities';

    protected $_primary_key = 'identity_id';

    protected $_table_columns = array(
        'identity_id' => array(),
        'user_id' => array(),
        'username' => array(),
        'password' => array(),
        'email' => array(),
        'status' => array()
    );

    protected $_belongs_to = array(
        'user' => array(
            'model' => 'user',
            'foreign_key' => 'user_id'
        )
    );

    // Array of reserved words, users can't choose as username
    protected $_reserved = array(
        'guest',
        'administrator',
        'admin',
        'user'
    );

    /**
     * Status constants
     */
    const STATUS_ACTIVE     = 'active';
    const STATUS_INACTIVE   = 'inactive';
    const STATUS_INVITED    = 'invited';

    /**
     * Defines validation rules
     *
     * @return  array
     */
    public function rules()
    {
        return array(
            'username' => array(
                array('not_empty'),
                array('min_length', array(':value', 4)),
                array('max_length', array(':value', 32)),
                array('regex', array(':value', '/^[A-Za-z0-9.]+$/')),
                array(array($this, 'username_available'), array(':value')),
                array(array($this, 'not_reserved'), array(':value')),
            ),
            'email' => array(
                array('not_empty'),
                array('email'),
                array(array($this, 'email_available'), array(':value')),
            )
        );
    }

    /**
     * Defines filters
     *
     * @return  array
     */
    public function filters()
    {
        return array(
            'username' => array(
                array('trim')
            ),
            'password' => array(
                array(array($this, 'hash'))
            ),
            'email' => array(
                array('trim')
            )
        );
    }

    /**
     * Hash a string
     *
     * @param   string  $value
     * @param   string  $salt
     * @return  string
     */
    public function hash($value, $salt = NULL)
    {
        return crypt($value, $salt);
    }

    /**
     * Check if the given username is available
     *
     * @param   string  $username
     * @return  bool
     */
    public function username_available($username)
    {
        return ! (bool) DB::select(array(DB::expr('COUNT("*")'), 'total_count'))
            ->from($this->_table_name)
            ->where('username', '=', $username)
            ->where($this->_primary_key, '!=', $this->pk())
            ->where('status', '!=', self::STATUS_INVITED)
            ->execute($this->_db)
            ->get('total_count');
    }

    /**
     * Check if the given email is available
     *
     * @param   string  $email
     * @return  bool
     */
    public function email_available($email)
    {
        return ! (bool) DB::select(array(DB::expr('COUNT("*")'), 'total_count'))
            ->from($this->_table_name)
            ->where('email', '=', $email)
            ->where($this->_primary_key, '!=', $this->pk())
            ->where('status', '!=', self::STATUS_INVITED)
            ->execute($this->_db)
            ->get('total_count');
    }

    /**
     * Check if a given word is not a reserved one, and it can be used as username
     *
     * @param   string  $word
     * @return  bool
     */
    public function not_reserved($word)
    {
        return ( ! in_array(strtolower($word), $this->_reserved) OR ($this->loaded())) AND (in_array($this->get('username'), $this->_reserved));
    }

    /**
     * Get password validation
     *
     * @param   $values
     * @return  Validation
     */
    public function get_password_validation($values)
    {
        return Validation::factory($values)
            ->rule('password', 'not_empty')
            ->rule('password', 'min_length', array(':value', 6))
            ->rule('password', 'max_length', array(':value', 32))
            ->rule('password', array($this, 'not_numeric'), array(':value'))
            ->rule('password_confirm', 'matches', array(':validation', ':field', 'password'));
    }

    /**
     * Check if a string contains not only digits.
     *
     * @param   string  $str
     * @return  bool
     */
    public function not_numeric($str)
    {
        return ! is_numeric($str);
    }
}

// END Kohana_Model_Identity
