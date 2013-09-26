<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_Model_User extends ORM {

    protected $_table_name = 'users';

    protected $_primary_key = 'user_id';

    protected $_table_columns = array(
        'user_id' => array(),
        'first_name' => array(),
        'last_name' => array(),
        'timezone' => array()
    );

    protected $_has_one = array(
        'identity' => array(
            'model'   => 'identity',
            'foreign_key' => 'user_id'
        ),
    );

    /**
     * Define validation rules
     *
     * @return  array
     */
    public function rules()
    {
        return array(
            'first_name' => array(
                array('not_empty')
            ),

            'timezone' => array(
                array('not_empty'),
                array(array($this, 'valid_timezone'), array(':value')),
            )
        );
    }

    /**
     * Define filters
     *
     * @return  array
     */
    public function filters()
    {
        return array(
            'first_name' => array(
                array('trim')
            ),

            'last_name' => array(
                array('trim')
            ),
        );
    }

    /**
     * Check if a string is a valid timezone identifier
     *
     * @param   string  $timezone_identifier
     * @return  bool
     */
    public function valid_timezone($timezone_identifier)
    {
        try
        {
            new DateTimeZone($timezone_identifier);

            return TRUE;
        }
        catch (Exception $e)
        {
            return FALSE;
        }
    }

    /**
     * Get data about a user
     *
     * @param   string  $field
     * @param   mixed   $value
     * @return  array
     */
    public function get_user_data_by($field, $value)
    {
        $columns = array(
            'users.user_id',
            'users.first_name',
            'users.last_name',
            'users.timezone',
            'user_identities.email',
            'user_identities.status'
        );

        $table_name = in_array($field, $this->_table_columns)
            ? 'users'
            : 'user_identities';

        $field = $table_name.'.'.$field;

        return DB::select_array($columns)
            ->from('users')
            ->join('user_identities')
            ->on('users.user_id', '=', 'user_identities.user_id')
            ->on($field, '=', DB::expr("'".$value."'"))
            ->as_assoc()
            ->execute($this->_db)
            ->current();
    }
}

// END Kohana_Model_User
