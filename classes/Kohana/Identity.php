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
     * @var string  Username
     */
    protected $_username;

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
        '__username'    => 'username',
        '__id'          => 'user_id'
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
    const ERROR_USERNAME_INVALID    = 'username_invalid';
	const ERROR_PASSWORD_INVALID    = 'password_invalid';
	const ERROR_IDENTITY_EMPTY      = 'identity_empty';
    const ERROR_IDENTITY_INACTIVE   = 'identity_inactive';
    const ERROR_USER_ID_INVALID     = 'user_id_invalid';
	
    /**
     * Construct
     *
     * @param   string  $username
     * @param   string  $password
     */
	protected function __construct($username = NULL, $password = NULL)
	{
		$this->_username 		    = $username;
		$this->_password 		    = $password;
		$this->_is_authenticated    = FALSE;
		$this->_states 			    = array();
	}

    /**
     * Authenticate the user
     *
     * @throws Auth_Exception
     */
	public function authenticate()
    {
		if (empty($this->_username) OR empty($this->_password))
		{
			// Empty identity
            $error_code = self::ERROR_IDENTITY_EMPTY;
            $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.$error_code);

            throw new Auth_Exception(Auth_Exception::E_INVALID_CREDENTIALS, $error_message);
		}
		else
		{
            // Find identity by username or email
            $identity = ORM::factory('Identity')
                ->where('username', '=', $this->_username)
                ->or_where('email', '=', $this->_username)
                ->find();
						
			if ( ! $identity->loaded())
			{
				// Wrong email or username
                $error_code = self::ERROR_USERNAME_INVALID;
                $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.$error_code);

                throw new Auth_Exception(Auth_Exception::E_INVALID_CREDENTIALS, $error_message);
			}
			else 
			{
                if ($identity->hash($this->_password, $identity->get('password')) != $identity->get('password'))
				{
					// Wrong password
                    $error_code = self::ERROR_PASSWORD_INVALID;
                    $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.$error_code);

                    throw new Auth_Exception(Auth_Exception::E_INVALID_CREDENTIALS, $error_message);
				}
				else 
				{
					if ($identity->get('status') != Model_Identity::STATUS_ACTIVE)
                    {
                        // Inactive identity
                        $error_code = self::ERROR_IDENTITY_INACTIVE;
                        $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.$error_code);

                        throw new Auth_Exception(Auth_Exception::E_INACTIVE_ACCOUNT, $error_message);
                    }
                    else
                    {
                        // Valid identity
                        $this->_is_authenticated = TRUE;

                        // Load the user data
                        foreach ($this->_states_to_load as $key => $value)
                        {
                            $this->set_state($key, $identity->get($value));
                        }

                        // The authentication was form based
                        $this->set_state('__auth_with', 'form');
                    }
				}
			}
		}
	}

    /**
     * Authenticate the user based on cookie.
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
        foreach ($this->_states_to_load as $key => $value)
        {
            $this->set_state($key, $identity->$value);
        }

        // The authentication was cookie based
        $this->set_state('__auth_with', 'cookie');

        // Automatically extend cookie lifetime
        $cookie->renew_cookie();
	}

    /**
     * Authenticate the user by user id
     * !!! Use this function only is special situations
     *
     * @param   int     $user_id
     * @throws  Auth_Exception
     */
    public function authenticate_with_id($user_id)
    {
        // Find identity by user id
        $identity = ORM::factory('Identity')
            ->where('user_id', '=', $user_id)
            ->find();

        if ( ! $identity->loaded())
        {
            throw new Auth_Exception(Auth_Exception::E_RESOURCE_NOT_FOUND, 'Can not find the given user.');
        }

        // Load the user data
        foreach ($this->_states_to_load as $key => $value)
        {
            $this->set_state($key, $identity->$value);
        }

        // The authentication was id based
        $this->set_state('__auth_with', 'id');
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
        return $this->get_state('__id');
    }

    /**
     * Returns an instance of the class.
     *
     * @param   string  $username
     * @param   string  $password
     * @return  Identity
     */
	public static function factory($username = NULL, $password = NULL)
	{
		return new Identity($username, $password);
	}
}

// END Kohana_Identity
