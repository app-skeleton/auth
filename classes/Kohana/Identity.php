<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_Identity {

    /**
     * @var string  Email
     */
    protected $_email;

    /**
     * @var string  Password
     */
    protected $_password;

    /**
     * @var bool    Whether the authentication was successful
     */
    protected $_is_authenticated;

    /**
     * States to load after authentication
     *
     * @var array
     */
    protected $_states_to_load = array(
        'user_id'
    );

    /**
     * List of user states
     *
     * @var array
     */
    protected $_states;

    /**
     * Error code constants
     */
    const ERROR_EMAIL_EMPTY         = 'email_empty';
    const ERROR_PASSWORD_EMPTY      = 'password_empty';
    const ERROR_EMAIL_INVALID       = 'email_invalid';
	const ERROR_PASSWORD_INVALID    = 'password_invalid';
    const ERROR_IDENTITY_INACTIVE   = 'identity_inactive';
    const ERROR_USER_ID_INVALID     = 'user_id_invalid';
	
    /**
     * Construct
     *
     * @param   string  $email
     * @param   string  $password
     */
	protected function __construct($email = NULL, $password = NULL)
	{
		$this->_email 		        = $email;
		$this->_password 		    = $password;
		$this->_is_authenticated    = FALSE;
		$this->_states 			    = array();
	}

    /**
     * Authenticate the user
     *
     * @throws  Auth_Exception
     * @return  Identity
     */
	public function authenticate()
    {
		if (empty($this->_email))
		{
			// Email is empty
            $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.self::ERROR_EMAIL_EMPTY);

            throw new Auth_Exception(Auth_Exception::E_INVALID_CREDENTIALS, $error_message, NULL, array(self::ERROR_EMAIL_EMPTY));
		}
        elseif (empty($this->_password))
        {
            // Password is empty
            $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.self::ERROR_PASSWORD_EMPTY);

            throw new Auth_Exception(Auth_Exception::E_INVALID_CREDENTIALS, $error_message, NULL, array(self::ERROR_PASSWORD_EMPTY));
        }
		else
		{
            // Find identity by email
            $identity = ORM::factory('Identity')
                ->where('email', '=', $this->_email)
                ->find();
						
			if ( ! $identity->loaded())
			{
				// Wrong email
                $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.self::ERROR_EMAIL_INVALID);

                throw new Auth_Exception(Auth_Exception::E_INVALID_CREDENTIALS, $error_message, NULL, array(self::ERROR_EMAIL_INVALID));
			}
			else 
			{
                if ($identity->hash($this->_password, $identity->get('password')) != $identity->get('password'))
				{
					// Wrong password
                    $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.self::ERROR_PASSWORD_INVALID);

                    throw new Auth_Exception(Auth_Exception::E_INVALID_CREDENTIALS, $error_message, NULL, array(self::ERROR_PASSWORD_INVALID));
				}
				else 
				{
					if ($identity->get('status') != Model_Identity::STATUS_ACTIVE)
                    {
                        // Inactive identity
                        $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.self::ERROR_IDENTITY_INACTIVE);

                        throw new Auth_Exception(Auth_Exception::E_INVALID_CREDENTIALS, $error_message, NULL, array(self::ERROR_IDENTITY_INACTIVE));
                    }
                    else
                    {
                        // Valid identity
                        $this->_is_authenticated = TRUE;

                        // Load the user data
                        foreach ($this->_states_to_load as $key)
                        {
                            $this->set_state($key, $identity->get($key));
                        }

                        // The authentication was form based
                        $this->set_state('__auth_with', 'form');
                    }
				}
			}
		}

        return $this;
	}

    /**
     * Authenticate the user based on cookie.
     *
     * @return  Identity
     */
	public function authenticate_with_cookie() 
	{		
		// Get the User_Cookie instance
        $cookie = User_Cookie::instance();

        // Try to load the cookie
        $cookie->load_cookie();

        // Valid login
        $this->_is_authenticated = TRUE;

        // Load identity
        $identity = ORM::factory('Identity')
            ->where('user_id', '=', $cookie->user_id())
            ->find();

        // Load the user states
        foreach ($this->_states_to_load as $key)
        {
            $this->set_state($key, $identity->get($key));
        }

        // The authentication was cookie based
        $this->set_state('__auth_with', 'cookie');

        // Automatically extend cookie lifetime
        $cookie->renew_cookie();

        return $this;
	}

    /**
     * Authenticate the user by user id
     * !!! Use this function only is special situations
     *
     * @param   int     $user_id
     * @throws  Kohana_Exception
     * @return  Identity
     */
    public function authenticate_with_id($user_id)
    {
        // Find identity by user id
        $identity = ORM::factory('Identity')
            ->where('user_id', '=', $user_id)
            ->find();

        if ( ! $identity->loaded())
        {
            throw new Kohana_Exception(
                'Can not find the user with id :user_id.', array(
                    ':user_id' => $user_id
                ), Kohana_Exception::E_RESOURCE_NOT_FOUND);
        }

        // Load the user data
        foreach ($this->_states_to_load as $key)
        {
            $this->set_state($key, $identity->get($key));
        }

        // The authentication was id based
        $this->set_state('__auth_with', 'id');

        return $this;
    }
	
	/**
     * Stores a variable in user states.
	 *
	 * @param	string  $key
	 * @param	mixed   $value
	 * @return	Kohana_Identity
     */
	public function set_state($key, $value)
	{				
		$this->_states[$key] = $value;
		
		return $this;
	}

    /**
     * Returns a variable from user states, or default.
	 *
	 * @param   string  $key
     * @param   mixed   $default
	 * @return mixed
     */
	public function get_state($key, $default = NULL)
	{
		return isset($this->_states[$key])
            ? $this->_states[$key]
            : $default;
	}
		
	/**
     * Returns the list of user states
	 *
     * @return  array
     */
	public function get_states()
	{		
		return $this->_states;
	}

    /**
     * Return the users id
     *
     * @return  mixed
     */
    public function id()
    {
        return $this->get_state('user_id');
    }

    /**
     * Returns an instance of the class.
     *
     * @param   string  $email
     * @param   string  $password
     * @return  Identity
     */
	public static function factory($email = NULL, $password = NULL)
	{
		return new Identity($email, $password);
	}
}

// END Kohana_Identity
