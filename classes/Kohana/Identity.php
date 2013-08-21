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
     * Username
     *
     * @var string
     */
    protected $_username;

    /**
     * Password
     *
     * @var string
     */
    protected $_password;

    /**
     * Whether the authentication was successful
     *
     * @var bool
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

    // Error codes
	const ERROR_USERNAME_INVALID = 1;
	const ERROR_PASSWORD_INVALID = 2;
	const ERROR_IDENTITY_EMPTY = 3;
    const ERROR_IDENTITY_INACTIVE = 4;
    const ERROR_USER_ID_INVALID = 5;
	
    /**
     * Construct
     *
     * @param   string  $username
     * @param   string  $password
     */
	protected function __construct($username = NULL, $password = NULL)
	{
		$this->_username 		= $username;
		$this->_password 		= $password;
		$this->_is_authenticated = FALSE;
		$this->_states 			= array();
	}

    /**
     * Authenticate the user
     *
     * @throws User_Identity_Exception
     */
	public function authenticate()
    {
		if ((empty($this->_username)) OR (empty($this->_password)))
		{
			// Empty identity
            $error_code = self::ERROR_IDENTITY_EMPTY;
            $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.$error_code);

            throw new User_Identity_Exception($error_message, array(), $error_code);
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
				// Wrong username
                $error_code = self::ERROR_USERNAME_INVALID;
                $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.$error_code);

                throw new User_Identity_Exception($error_message, array(), $error_code);
			}
			else 
			{
                if ($identity->hash($this->_password, $identity->password) != $identity->password)
				{
					// Wrong password
                    $error_code = self::ERROR_PASSWORD_INVALID;
                    $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.$error_code);

                    throw new User_Identity_Exception($error_message, array(), $error_code);
				}
				else 
				{
					if ($identity->status != 'active')
                    {
                        // Inactive identity
                        $error_code = self::ERROR_IDENTITY_INACTIVE;
                        $error_message = Kohana::message('auth/'.i18n::lang().'/auth', 'login.error.'.$error_code);

                        throw new User_Identity_Exception($error_message, array(), $error_code);
                    }
                    else
                    {
                        // Valid identity
                        $this->_is_authenticated = TRUE;

                        // Load the user data
                        foreach ($this->_states_to_load as $key => $value)
                        {
                            $this->set_state($key, $identity->$value);
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

        $cookie->load();

        // Valid login
        $this->_is_authenticated = TRUE;

        // Load identity
        $identity = ORM::factory('Identity')->where('user_id', '=', $cookie->user_id())->find();

        // Load the user states
        foreach ($this->_states_to_load as $key => $value)
        {
            $this->set_state($key, $identity->$value);
        }

        // The authentication was cookie based
        $this->set_state('__auth_with', 'cookie');

        // Automatically extend cookie lifetime
        $cookie->extend();
	}

    /**
     * Authenticate the user by user id
     * !!! Use this function only is special situations
     *
     * @param $user_id
     * @throws User_Identity_Exception
     */
    public function authenticate_with_id($user_id)
    {
        // Find identity by user id
        $identity = ORM::factory('Identity')
            ->where('user_id', '=', $user_id)
            ->find();

        if ( ! $identity->loaded())
        {
            throw new User_Identity_Exception('Invalid user id: '.$user_id, array(), self::ERROR_USER_ID_INVALID);
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
	 * @param	$key
	 * @param	$value
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
	 * @param $key
     * @param $default
	 * @return mixed
     */
	public function get_state($key, $default = NULL)
	{
		return (isset($this->_states[$key])) ? $this->_states[$key] : $default;
	}
		
	/**
     * Returns the list of user states
	 *
     * @return array
     */
	public function get_states()
	{		
		return $this->_states;
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
