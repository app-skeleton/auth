<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_User_Manager extends Service_Manager {

    /**
     * Sign up a new user
     *
     * @param   array   $values
     * @throws  Validation_Exception
     * @throws  Auth_Exception
     * @throws  Exception
     * @return  array   An array containing the user and the identity model data
     */
    public function signup_user($values)
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
        return $this->_save_user($user_model, $identity_model, $values);
    }

    /**
     * Update user
     *
     * @param   int     $user_id
     * @param   array   $values
     * @throws  Kohana_Exception
     * @throws  Validation_Exception
     * @throws  Exception
     * @return  array   An array containing the user and the identity models data as arrays
     */
    public function update_user($user_id, $values)
    {
        // Load the user
        $user_model = ORM::factory('User', $user_id);

        if ( ! $user_model->loaded())
        {
            throw new Kohana_Exception(
                'Can not find the user with id :user_id.', array(
                ':user_id' => $user_id
            ), Kohana_Exception::E_RESOURCE_NOT_FOUND);
        }

        // Load the identity
        $identity_model = ORM::factory('Identity')
            ->where('user_id', '=', $user_id)
            ->find();

        // Save the user
        return $this->_save_user($user_model, $identity_model, $values);
    }

    /**
     * Validate and save user to the database
     *
     * @param   ORM     $user_model
     * @param   ORM     $identity_model
     * @param   array   $values
     * @return  array
     * @throws  Validation_Exception
     * @throws  Exception
     */
    protected function _save_user($user_model, $identity_model, $values)
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
            $user_errors = $e->errors('models/'.i18n::lang().'/auth', FALSE);
        }

        try
        {
            // Validate the identity
            $identity_model->values($values);
            $identity_model->check($identity_model->get_password_validation($values));
        }
        catch (ORM_Validation_Exception $e)
        {
            $identity_errors = $e->errors('models/'.i18n::lang().'/auth', FALSE);
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
            throw new Validation_Exception($errors);
        }

        // Validation passes, begin transaction
        $this->begin_transaction();

        try
        {
            // Save user
            $user_model->save();

            if ( ! $identity_model->loaded())
            {
                // Link the identity to the user
                $identity_model->set('user_id', $user_model->pk());
            }

            // Make the identity active
            $identity_model->set('status', Model_Identity::STATUS_ACTIVE);

            // Save identity
            $identity_model->save();

            // Everything was going fine, commit
            $this->commit_transaction();

            return array(
                'user' => $user_model->as_array(),
                'identity' => $identity_model->as_array()
            );
        }
        catch (Exception $e)
        {
            // Something went wrong, rollback
            $this->rollback_transaction();

            // Re-throw the exception
            throw $e;
        }
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
     * @param   string  $column     Possible values: user_id, email
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
}

// END Kohana_User_Manager
