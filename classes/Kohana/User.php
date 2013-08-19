<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_User {

	// Session instance
	protected $_session;

    // Session key
    protected $_session_key;

	// Identity
	protected $_identity;

    // States to load from db
    protected $_states_to_load = array('first_name', 'last_name', 'timezone');

    // Singleton instance
    protected static $_instance;

    /**
     * Create a new instance
     */
	protected function __construct()
	{
        // Create own session instance
        $this->_session = Session::instance();

        // Get session key from config
        $this->_session_key = Kohana::$config->load('auth/user')->get('session_key');
	}

    /**
     * Log in the user
     *
     * @param   Identity    $identity
     * @param   bool        $create_cookie
     * @param   int         $cookie_lifetime
     */
	public function login(Identity $identity, $create_cookie = FALSE, $cookie_lifetime = NULL)
	{
		$this->_identity = $identity;

		// Copy identity states in user states
		foreach ($this->_identity->get_states() as $key => $value)
		{
			// Copy all states from identity
            $this->set_state($key, $value);
		}

        // Call after_login
        $this->_after_login();

        if ($create_cookie)
        {
            // Create "remember me" cookie
            $this->_create_cookie($cookie_lifetime);
        }
	}

    /**
     * Log out the user.
     */
	public function logout()
	{
		if ($this->logged_in())
        {
            $this->clear_states();

            // Delete "remember me" cookie if exists
            $this->_delete_cookie();
        }
	}

	/**
     * Check if the user is logged in
     *
     * @return  bool
     */
	public function logged_in()
	{
		return ($this->get_state('__id') !== NULL);
	}

    /**
     * Get (or optionally set) the unique name (username) for the user
     *
     * @param   string  $name
     * @return  string
     */
	public function name($name = NULL)
	{
		if ( ! empty($name))
        {
            $this->set_state('__name', $name);
        }

        return ($this->get_state('__name')) ? $this->get_state('__name') : Kohana::$config->load('auth/user')->get('guest_name');
	}

    /**
     * Return the unique id for the user
	 *
     * @return  mixed
     */
	public function id()
	{
        return $this->get_state('__id');
	}

    /**
     * Set/Get the authentication method used for login
     *
     * @param   string  $auth_with
     * @return  mixed
     */
    public function authenticated_with($auth_with = NULL)
    {
        if ($auth_with)
        {
            $this->set_state('__auth_with', $auth_with);
        }

        return $this->get_state('__auth_with');
    }

    /**
     * Store a variable in user's state.
     *
     * @param   string  $key
     * @param   mixed   $value
     */
	public function set_state($key, $value)
	{
		// Get user data
        $user_data = $this->_session->get($this->_session_key, array());

		// Set user state
		$user_data[$key] = $value;

		// Write states back to session
		$this->_session->set($this->_session_key, $user_data);
	}

	/**
     * Return the specified user state, or $default.
	 *
 	 * @param   string  $key
	 * @param   mixed    $default
	 * @return  mixed
     */
	public function get_state($key, $default = NULL)
	{
		$user_data = $this->_session->get($this->_session_key, array());

		return (isset($user_data[$key])) ? $user_data[$key] : $default;
	}

	/**
     * Delete a user state.
	 *
	 * @param   $key
     */
	public function delete_state($key)
	{
		// Get user states
		$user_data = $this->_session->get($this->_session_key, array());

		// Delete state
		unset($user_data[$key]);

		// Write states back to session
		$this->_session->set($this->_session_key, $user_data);
	}

	/**
     * Return the list of user states
	 *
     * @return  array
     */
	public function get_states()
	{
		return $this->_session->get($this->_session_key, array());
	}

	/**
     * Clear user states
     */
	public function clear_states()
	{
		$this->_session->set($this->_session_key, array());
	}

    /**
     * Set states to load from db
     *
     * !!! You should call this function before calling the login method
     *
     * @param   array   $states
     * @return  array
     */
    public function states_to_load($states = NULL)
    {
        if ($states)
        {
            $this->_states_to_load = $states;
        }

        return $states;
    }

    /**
     * Convert a date/time to users timezone
     *
     * @param   string      $original_datetime
     * @param   string      $original_timezone
     * @param   string      $format
     * @return  string
     */
    public function datetime($original_datetime, $original_timezone = 'UTC', $format = 'Y-m-d H:i:s')
    {
        // Get users timezone
        $user_timezone = $this->get_state('timezone');

        // Instantiate the DateTime object, setting it's date, time and time zone.
        $datetime = new DateTime($original_datetime, new DateTimeZone($original_timezone));

        // Set timezone
        $datetime->setTimeZone(new DateTimeZone($user_timezone));

        // Return the formatted date/time string
        return $datetime->format($format);
    }

    /**
     * Load user states from db
     *
     * @return  void
     */
    protected function _after_login()
    {
        $data = ORM::factory('User', $this->id())->as_array();
        foreach ($this->_states_to_load as $state)
        {
            if (isset($data[$state]))
            {
                $this->set_state($state, $data[$state]);
            }
        }
    }

    /**
     * Create the "remember me" cookie.
     *
     * @param   int $cookie_lifetime
     */
    protected function _create_cookie($cookie_lifetime = NULL)
    {
        $cookie = User_Cookie::instance();
        $cookie->create($this->id(), $cookie_lifetime);
    }

    /**
     * Delete the "remember me" cookie.
     */
    protected function _delete_cookie()
    {
        $cookie = User_Cookie::instance();

        try
        {
            $cookie->load();
            $cookie->delete();

        }
        catch (User_Cookie_Exception $e)
        {
            // Do nothing
        }
    }

	/**
     * Returns a singleton instance of the class.
	 *
	 * @return  User
     */
	public static function instance()
	{
		if ( ! User::$_instance instanceof User)
		{
			User::$_instance = new User();
		}

		return User::$_instance;
	}
}

// END Kohana_User
