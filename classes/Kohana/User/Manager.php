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

    // Singleton instance
    protected static $_instance;

    /**
     * Sign up a new user
     *
     * @param   array   $values
     * @throws  User_Validation_Exception
     * @throws  Kohana_Exception
     * @return  array   An array containing the user and the identity models data as arrays
     */
    public function signup($values)
    {
        // Validation errors
        $errors = array();
        $user_errors = array();
        $identity_errors = array();

        // Create user, and get validation errors
        $user_model = ORM::factory('User');
        $user_model->values($values);

        try
        {
            $user_model->check();
        }
        catch (ORM_Validation_Exception $e)
        {
            $user_errors = $e->errors('models/'.i18n::lang().'/user', FALSE);
        }

        // Create identity, and get validation errors
        $identity_model = ORM::factory('Identity');
        $identity_model->values($values);

        try
        {
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

            $errors = array_merge($user_errors, $identity_errors);
        }

        // If validation fails, throw an exception
        if ($errors)
        {
            throw new User_Validation_Exception($errors);
        }            

        // Validation passes, save the user, and the identity
        $db = Database::instance();
        $db->begin();

        try {
            $user_model->save();

            // Setup identity
            $identity_model->user_id = $user_model->pk();
            $identity_model->status = 'active';

            // Save identity
            $identity_model->save();

            // Insert successful, commit the changes
            $db->commit();

            return array($user_model->as_array(), $identity_model->as_array());
        }
        catch (Database_Exception $e)
        {
            // Insert failed, roll back changes
            $db->rollback();

            throw new Kohana_Exception('Something went wrong. Please try again later.');
        }
    }

    /**
     * Update user
     *
     * @param   int     $user_id
     * @param   array   $values
     * @throws  Kohana_Exception
     * @throws  User_Validation_Exception
     * @return  array   An array containing the user and the identity models data as arrays
     */
    public function update($user_id, $values)
    {
        // User id must be valid
        if ( ! is_numeric($user_id))
        {
            throw new Kohana_Exception('Invalid user id.');
        }

        // Validation errors
        $errors = array();
        $user_errors = array();
        $identity_errors = array();

        // Load the user
        $user_model = ORM::factory('User', $user_id);

        if ( ! $user_model->loaded())
        {
            throw new Kohana_Exception('Can not find user by id: '.$user_id);
        }

        // Get validation errors
        $user_model->values($values);

        try
        {
            $user_model->check();
        }
        catch (ORM_Validation_Exception $e)
        {
            $user_errors = $e->errors('models/'.i18n::lang().'/user', FALSE);
        }


        // Load identity, and get validation errors
        $identity_model = ORM::factory('Identity')
            ->where('user_id', '=', $user_id)
            ->find();

        // If not password set, we don't have to update password
        if (empty($values['password']))
        {
            unset($values['password']);
            unset($values['password_confirm']);
            $extra_validation = NULL;
        }
        else
        {
            $extra_validation = $identity_model->get_password_validation($values);
        }

        $identity_model->values($values);

        try
        {
            $identity_model->check($extra_validation);
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

            $errors = array_merge($user_errors, $identity_errors);
        }

        // If validation fails, throw an exception
        if ($errors)
        {
            throw new User_Validation_Exception($errors);
        }

        // Validation passes, save the user, and the identity
        $db = Database::instance();
        $db->begin();

        try
        {
            $user_model->save();
            $identity_model->save();

            // Update successful, commit the changes
            $db->commit();

            return array($user_model->as_array(), $identity_model->as_array());
        }
        catch (Database_Exception $e)
        {
            // Update failed, roll back changes
            $db->rollback();

            throw new Kohana_Exception('Something went wrong. Please try again later.');
        }
    }

    /**
     * Get user data
     *
     * @param   mixed   $find_by_value
     * @param   array   $fields
     * @param   string  $find_by_field
     * @return  mixed
     */
    public function get_data($find_by_value, $fields = array('*'), $find_by_field = 'users.user_id')
    {
        return DB::select_array($fields)
            ->from('users')
            ->join('user_identities')
            ->on('users.user_id', '=', 'user_identities.user_id')
            ->where($find_by_field, '=', DB::expr($find_by_value))
            ->execute()
            ->current();
    }

    /**
     * Garbage collector
     */
    public function garbage_collector()
    {
        // Delete outdated cookies
        DB::delete('auth_cookies')
            ->where('valid_until', '<', date('Y-m-d H:i:s'))
            ->execute();

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

//END Kohana_User_Manager