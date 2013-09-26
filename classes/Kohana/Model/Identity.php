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
        'email' => array(),
        'password' => array(),
        'status' => array()
    );

    protected $_belongs_to = array(
        'user' => array(
            'model' => 'user',
            'foreign_key' => 'user_id'
        )
    );

    /**
     * Status constants
     */
    const STATUS_ACTIVE     = 'ACTIVE';
    const STATUS_INACTIVE   = 'INACTIVE';
    const STATUS_INVITED    = 'INVITED';

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
            'email' => array(
                array('trim')
            ),
            'password' => array(
                array(array($this, 'hash'))
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
