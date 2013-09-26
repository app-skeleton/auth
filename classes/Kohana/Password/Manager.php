<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_Password_Manager extends Service_Manager {

    /**
     * Send recovery email
     *
     * @param   string  $email
     * @throws  Validation_Exception
     */
    public function recover_password($email)
    {
        // Load config
        $config = Kohana::$config->load('auth/recovery');

        try
        {
            // Generate secure key
            $secure_key = Text::random('alnum', 64);

            // Create a password recovery link
            ORM::factory('Password_Recovery_Link')
                ->set('email', $email)
                ->set('secure_key', $secure_key)
                ->set('expires_on', date('Y-m-d H:i:s', time() + $config['link']['lifetime']))
                ->save();

            // Get data about the user
            $user_data = ORM::factory('User')->get_user_data_by('email', $email);

            // Prepare data for the recovery email
            $data = array(
                'user' => array(
                    'first_name' => $user_data['first_name'],
                    'last_name' => $user_data['last_name'],
                    'email' => $email
                ),
                'url' => URL::map('auth.reset', array($secure_key))
            );

            // Send the recovery email
            Password_Recovery_Email::factory($email, $data)->send();

        }
        catch (ORM_Validation_Exception $e)
        {
            $errors = $e->errors('models/'.i18n::lang().'/auth', FALSE);
            throw new Validation_Exception($errors);
        }
    }

    /**
     * Check if a recovery link is valid
     *
     * @param   string  $secure_key
     * @throws  Auth_Exception
     */
    public function check_recovery_link($secure_key)
    {
        $link_model = ORM::factory('Password_Recovery_Link')
            ->where('secure_key', '=', $secure_key)
            ->and_where('expires_on', '>', date('Y-m-d H:i:s'))
            ->find();

        if ( ! $link_model->loaded())
        {
            throw new Auth_Exception(Auth_Exception::E_INVALID_RECOVERY_LINK);
        }
    }

    /**
     * Reset password and return the email associated with the given secure key
     *
     * @param   string  $secure_key
     * @param   string  $password
     * @param   string  $password_confirm
     * @throws  Auth_Exception
     * @throws  Validation_Exception
     * @return  string
     */
    public function reset_password($secure_key, $password, $password_confirm)
    {
        $link_model = ORM::factory('Password_Recovery_Link')
            ->where('secure_key', '=', $secure_key)
            ->and_where('expires_on', '>', date('Y-m-d H:i:s'))
            ->find();

        if ( ! $link_model->loaded())
        {
            throw new Auth_Exception(Auth_Exception::E_INVALID_RECOVERY_LINK);
        }

        // Get the email
        $email = $link_model->get('email');

        // Find the identity by email
        $identity_model = ORM::factory('Identity')
            ->where('email', '=', $email)
            ->find();

        // Force "changed" status
        $identity_model->set('password', '');

        // Change password
        $identity_model->set('password', $password);

        try
        {
            // Save the identity
            $identity_model->save($identity_model->get_password_validation(array(
                'password' => $password,
                'password_confirm' => $password_confirm
            )));
        }
        catch (ORM_Validation_Exception $e)
        {
            $errors = $e->errors('models/'.i18n::lang().'/auth', FALSE);
            throw new Validation_Exception($errors['_external']);
        }

        // Delete all password recovery links for this user
        $link_model->delete_all($email);

        return $email;
    }

    /**
     * Garbage collector
     */
    public function garbage_collector()
    {
        // Delete outdated password recovery links
        ORM::factory('Password_Recovery_Link')->garbage_collector(time());
    }
}

// END Kohana_Password_Manager
