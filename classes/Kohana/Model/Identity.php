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
                array('max_length', array(':value', 64)),
                array(array($this, 'unique_username'), array(':value')),
                array(array($this, 'not_reserved'), array(':value')),
            ),
            'email' => array(
                array('not_empty'),
                array('max_length', array(':value', 127)),
                array('email'),
                array(array($this, 'unique_email'), array(':value')),
            )
        );
    }

    /**
     *  Defines filters
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

    // Check if username is available
    public function unique_username($username)
    {
        return $this->_unique_field('username', $username);
    }

    // Check if email is available
    public function unique_email($email)
    {
        return $this->_unique_field('email', $email);
    }

    /**
     * Check whether a field is unique
     *
     * @param   string  $field
     * @param   string  $value
     * @return  bool
     */
    protected function _unique_field($field, $value)
    {
        return ! (bool) DB::select(array(DB::expr('COUNT("*")'), 'total_count'))
            ->from($this->_table_name)
            ->where($field, '=', $value)
            ->where($this->_primary_key, '!=', $this->pk())
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
        return (( ! in_array(strtolower($word), $this->_reserved)) OR (($this->loaded())) AND (in_array($this->username, $this->_reserved))) ;
    }

    // Password validation rules
    public function get_password_validation($values)
    {
        return Validation::factory($values)
            ->rule('password', 'not_empty')
            ->rule('password', 'min_length', array(':value', 6))
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