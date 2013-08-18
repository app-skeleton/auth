<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_Password_Manager {

    // Singleton instance
    protected static $_instance;

    /**
     * Send recovery email
     *
     * @param   $email
     */
    public function recover($email)
    {
        Password_Recovery_Email::factory($email)->send();
    }

    /**
     * Get the recovery email associated with a secure key
     *
     * @param $secure_key
     * @return string
     */
    public function get_recovery_email($secure_key)
    {
        return ORM::factory('Password_Recovery_Link')->get_email($secure_key);
    }

    /**
     * Reset password
     *
     * @param   string  $email
     * @param   string  $password
     * @param   string  $password_confirm
     * @throws  User_Validation_Exception
     */
    public function reset($email, $password, $password_confirm)
    {
        // Find the identity by email
        $identity_model = ORM::factory('Identity')
            ->where('email', '=', $email)
            ->find();

        // Force "changed" status
        $identity_model->password = '';

        // Change password
        $identity_model->password = $password;

        // Try to save
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
            $errors = $e->errors('models/'.i18n::lang().'/user', FALSE);
            throw new User_Validation_Exception($errors['_external']);
        }

        // Delete all password recovery links of the user
        $this->delete_recovery_links($identity_model->email);
    }

    /**
     * Delete all recovery links for a given email address
     *
     * @param   $email
     * @return  void
     */
    public function delete_recovery_links($email)
    {
        DB::delete('password_recovery_links')
            ->where('email', '=', $email)
            ->execute();
    }

    /**
     * Garbage collector
     */
    public function garbage_collector()
    {
        // Delete outdated password recovery links
        DB::delete('password_recovery_links')
            ->where('valid_until', '<', date('Y-m-d H:i:s'))
            ->execute();
    }

    /**
     * Returns a singleton instance of the class.
     *
     * @return  Password_Manager
     */
    public static function instance()
    {
        if ( ! Password_Manager::$_instance instanceof Password_Manager)
        {
            Password_Manager::$_instance = new Password_Manager();
        }

        return Password_Manager::$_instance;
    }
}

//END Kohana_Password_Manager