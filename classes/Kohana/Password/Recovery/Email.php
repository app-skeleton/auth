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
     * @var string  The email address to send the recovery email to
     */
    protected $_email;

    /**
     * @var array   The data to render the email template with
     */
    protected $_data;

    /**
     * Construct
     *
     * @param   string  $email
     * @param   array   $data
     */
    protected function __construct($email, $data)
    {
        $this->_email = $email;
        $this->_data = $data;
    }

    /**
     * Send the recovery email
     */
    public function send()
    {
        $view = View::factory('auth/email/'.i18n::lang().'/recover', $this->_data);
        $config = Kohana::$config->load('auth/recovery');

        Email::factory($config['email']['subject'], $view->render(), 'text/html')
            ->to($this->_email)
            ->from($config['email']['sender']['email'], $config['email']['sender']['name'])
            ->send();
    }

    /**
     * Factory
     *
     * @param   string  $email
     * @param   array   $data
     * @return  Password_Recovery_Email
     */
    public static function factory($email, $data)
    {
        return new Password_Recovery_Email($email, $data);
    }
}

// END Kohana_Password_Recovery_Email
