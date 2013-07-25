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
        'last_name' => array()
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
}

// END Kohana_Model_User