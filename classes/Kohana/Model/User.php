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
                array(array($this, 'full_name'), array(':value', ':validation')),
                array('not_empty')
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
     * Validate the full name
     *
     * @param   string      $value
     * @param   Validation  $validation
     * @return  bool
     */
    public function full_name($value, $validation)
    {
        if (empty($value))
        {
            // Create error manually
            $validation->label('full_name', 'full_name');
            $validation->error('full_name', 'not_empty');
        }
        return TRUE;
    }

    /**
     * Get data about a user
     *
     * @param   int     $user_id
     * @param   array   $columns
     * @return  array
     */
    public function get_user_data($user_id, $columns)
    {
        $columns = isset($columns)
            ? $columns
            : array(
                'users.user_id',
                'users.first_name',
                'users.last_name',
                'users.timezone',
                'user_identities.username',
                'user_identities.email',
                'user_identities.status',
            );

        return DB::select_array($columns)
            ->from('users')
            ->join('user_identities')
            ->on('users.user_id', '=', 'user_identities.user_id')
            ->on('users.user_id', '=', DB::expr($user_id))
            ->execute()
            ->current();
    }

    /**
     * Get the user id by column and value
     *
     * @param   string  $column
     * @param   mixed   $value
     * @return  int
     */
    public function get_user_id_by($column, $value)
    {
        return DB::select('users.user_id')
            ->from('users')
            ->join('user_identities')
            ->on('users.user_id', '=', 'user_identities.user_id')
            ->where($column, '=', $value)
            ->execute()
            ->get('user_id');
    }

    /**
     * Begin a transaction
     */
    public function begin()
    {
        $this->_db->begin();
    }

    /**
     * Commit a transaction
     */
    public function commit()
    {
        $this->_db->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback()
    {
        $this->_db->rollback();
    }
}

// END Kohana_Model_User