<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_Password_Recovery_Email {

    /**
     * Identity
     *
     * @var Identity
     */
    protected $_identity;

    /**
     * Construct
     *
     * @param   string  $email
     * @throws  Password_Recovery_Email_Exception
     */
    public function __construct($email)
    {
        if (empty($email))
        {
            // No email provided
            throw new Password_Recovery_Email_Exception(Kohana::message('auth/'.i18n::lang().'/auth', 'recover.no_email'));
        }
        else
        {
            // Check if the provided email is valid
            $identity = ORM::factory('Identity')->where('email', '=', $email)->find();
            if ( ! $identity->loaded())
            {
                // Non existing user
                throw new Password_Recovery_Email_Exception(
                    strtr(Kohana::message('auth/'.i18n::lang().'/auth', 'recover.invalid_email'), array(':email' => $email))
                );
            }
            else
            {
                // Existing user
                $this->_identity = $identity;
            }
        }
    }

    /**
     * Send the recovery email
     */
    public function send()
    {
        $config = Kohana::$config->load('auth/recovery');
        $secure_key = ORM::factory('Password_Recovery_Link')
            ->generate($this->_identity->email)
            ->save()
            ->secure_key();

        $user_model = $this->_identity->user;
        $view = View::factory('auth/email/'.i18n::lang().'/recover');
        $view->name = $user_model->first_name.' '.$user_model->last_name;
        $view->email = $this->_identity->email;
        $view->url = $config['link']['url'].$secure_key;

        Email::factory($config['email']['subject'], $view->render(), 'text/html')
            ->to($this->_identity->email)
            ->from($config['email']['sender']['email'], $config['email']['sender']['name'])
            ->send();
    }

    /**
     * Factory
     *
     * @param   string  $email
     * @return  Password_Recovery_Email
     */
    public static function factory($email)
    {
        return new Password_Recovery_Email($email);
    }
}

// END Kohana_Password_Recovery_Email