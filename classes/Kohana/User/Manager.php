<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_User_Manager {

    /**
     * @var User_Manager    Singleton instance
     */
    protected static $_instance;

    /**
     * @var string          Database config group
     */
    protected $_db_group = NULL;

    /**
     * @var Database        Database instance
     */
    protected $_db;

    /**
     * Construct
     */
    protected function __construct()
    {
        $this->_db = Database::instance($this->_db_group);
    }

    /**
     * Sign up a new user
     *
     * @param   array   $values
     * @param   bool    $transactional
     * @throws  Auth_Validation_Exception
     * @throws  Auth_Exception
     * @throws  Exception
     * @return  array   An array containing the user and the identity model data
     */
    public function signup_user($values, $transactional = TRUE)
    {
        $email = Arr::get($values, 'email');
        $identity_model = ORM::factory('Identity');

        // Check if a user already exists with the given email
        if ( ! empty($email))
        {
            $identity_model
                ->where('email', '=', $email)
                ->find();

            if ($identity_model->loaded() && $identity_model->get('status') != Model_Identity::STATUS_INVITED)
            {
                throw new Auth_Exception(Auth_Exception::E_USER_IS_REGISTERED);
            }
        }

        $user_model = $identity_model->loaded()
            ? ORM::factory('User', $identity_model->get('user_id'))
            : ORM::factory('User');

        // Save the user
        return $this->_save_user($user_model, $identity_model, $values, $transactional);
    }

    /**
     * Update user
     *
     * @param   int     $user_id
     * @param   array   $values
     * @param   bool    $transactional
     * @throws  Auth_Exception
     * @throws  Auth_Validation_Exception
     * @throws  Exception
     * @return  array   An array containing the user and the identity models data as arrays
     */
    public function update_user($user_id, $values, $transactional = TRUE)
    {
        // Load the user
        $user_model = ORM::factory('User', $user_id);

        if ( ! $user_model->loaded())
        {
            throw new Auth_Exception(Auth_Exception::E_RESOURCE_NOT_FOUND, 'Can not find the given user.');
        }

        // Load the identity
        $identity_model = ORM::factory('Identity')
            ->where('user_id', '=', $user_id)
            ->find();

        // Save the user
        return $this->_save_user($user_model, $identity_model, $values, $transactional);
    }

    /**
     * Validate and save user to the database
     *
     * @param   ORM     $user_model
     * @param   ORM     $identity_model
     * @param   array   $values
     * @param   bool    $transactional
     * @return  array
     * @throws  Auth_Validation_Exception
     * @throws  Exception
     */
    protected function _save_user($user_model, $identity_model, $values, $transactional)
    {
        $user_errors = array();
        $identity_errors = array();

        try
        {
            // Validate the user
            $user_model->values($values);
            $user_model->check();
        }
        catch (ORM_Validation_Exception $e)
        {
            $user_errors = $e->errors('models/'.i18n::lang().'/user', FALSE);
        }

        try
        {
            // Validate the identity
            $identity_model->values($values);
            $identity_model->check($identity_model->get_password_validation($values));
        }
        catch (ORM_Validation_Exception $e)
        {
            $identity_errors = $e->errors('models/'.i18n::lang().'/user', FALSE);
            if (isset($identity_errors['_external']))
            {
                $identity_external_errors = $identity_errors['_external'];
                unset($identity_errors['_external']);
                $identity_errors = array_merge($identity_errors, $identity_external_errors);
            }
        }

        // Merge user and identity validation errors
        $errors = array_merge($user_errors, $identity_errors);

        // If validation fails, throw an exception
        if ( ! empty($errors))
        {
            throw new Auth_Validation_Exception($errors);
        }

        // Validation passes, begin transaction
        if ($transactional)
        {
            $this->db()->begin();
        }

        try
        {
            // Save user
            $user_model->save();

            if ( ! $identity_model->loaded())
            {
                // Setup identity
                $identity_model->set('user_id', $user_model->pk());
                $identity_model->set('status', Model_Identity::STATUS_ACTIVE);
            }

            // Save identity
            $identity_model->save();

            // Everything was going fine, commit
            if ($transactional)
            {
                $this->db()->commit();
            }

            return array(
                'user' => $user_model->as_array(),
                'identity' => $identity_model->as_array()
            );
        }
        catch (Exception $e)
        {
            // Something went wrong, rollback
            if ($transactional)
            {
                $this->db()->rollback();
            }

            // Re-throw the exception
            throw $e;
        }
    }

    /**
     * Check if the given username is available
     *
     * @param   string  $username
     * @return  bool
     */
    public function username_available($username)
    {
        return ORM::factory('Identity')->username_available($username);
    }

    /**
     * Check if the given email is available
     *
     * @param   string  $email
     * @return  bool
     */
    public function email_available($email)
    {
        return ORM::factory('Identity')->email_available($email);
    }

    /**
     * Get user data
     *
     * @param   int     $user_id
     * @return  array
     */
    public function get_user_data($user_id)
    {
        return ORM::factory('User')->get_user_data_by('user_id', $user_id);
    }

    /**
     * Get the user id by column and value
     *
     * @param   string  $column     Possible values: user_id, username, email
     * @param   mixed   $value
     * @return  int
     */
    public function get_user_data_by($column, $value)
    {
        return ORM::factory('User')->get_user_data_by($column, $value);
    }

    /**
     * Garbage collector
     */
    public function garbage_collector()
    {
        // Delete outdated cookies
        ORM::factory('User_Cookie')->garbage_collector(time());
    }

    /**
     * Get the database instance
     *
     * @return  Database
     */
    public function db()
    {
        return $this->_db;
    }

    /**
     * Returns a singleton instance of the class.
     *
     * @return	User_Manager
     */
    public static function instance()
    {
        if ( ! User_Manager::$_instance instanceof User_Manager)
        {
            User_Manager::$_instance = new User_Manager();
        }

        return User_Manager::$_instance;
    }
}

// END Kohana_User_Manager
