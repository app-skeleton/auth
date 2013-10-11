<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_User_Cookie {

    /**
     * @var User_Cookie             Singleton instance
     */
    protected static $_instance;

    /**
     * @var string                  Cookie name
     */
    protected $_cookie_name;

    /**
     * @var int                     Default cookie lifetime in seconds
     */
    protected $_default_cookie_lifetime;

    /**
     * @var Model_User_Cookie       The cookie model instance
     */
    protected $_cookie_model;

    /**
     * Construct
     */
    protected function __construct()
    {
        $config = Kohana::$config->load('auth/cookie');
        $this->_cookie_name = $config['name'];
        $this->_default_cookie_lifetime = $config['lifetime'];
    }

    /**
     * Try to load the cookie
     *
     * @throws  Auth_Exception
     */
    public function load_cookie()
    {
        // Get cookie value
        $cookie_value = Cookie::get($this->_cookie_name);

        // Check if cookie is valid
        if ($cookie_value !== NULL)
        {
            $cookie_model = ORM::factory('User_Cookie')
                ->where('secure_key', '=', $cookie_value)
                ->where('expires_on', '>', date('Y-m-d H:i:s'))
                ->find();

            if ( ! $cookie_model->loaded())
            {
                // Cookie is invalid (delete it)
                Cookie::delete($this->_cookie_name);

                throw new Auth_Exception(Auth_Exception::E_INVALID_COOKIE);
            }

            // Cookie is valid
            $this->_cookie_model = $cookie_model;
        }
        else
        {
            throw new Auth_Exception(Auth_Exception::E_INVALID_COOKIE);
        }
    }

    /**
     * Create a new cookie
     *
     * @param   int $user_id
     * @param   int $cookie_lifetime
     */
    public function create_cookie($user_id, $cookie_lifetime = NULL)
    {
        // Set cookie lifetime
        $cookie_lifetime = $cookie_lifetime ?: $this->_default_cookie_lifetime;

        // Generate new secure key
        $secure_key = Text::random('alnum', 64);

        // Create a new cookie
        Cookie::set(
            $this->_cookie_name,
            $secure_key,
            $cookie_lifetime
        );

        $this->_cookie_model = ORM::factory('User_Cookie')
            ->set('user_id', $user_id)
            ->set('secure_key', $secure_key)
            ->set('expires_on', date('Y-m-d H:i:s', time() + $cookie_lifetime))
            ->save();
    }

    /**
     * Renew the existing cookie
     *
     * @param   int $cookie_lifetime
     * @throws  Kohana_Exception
     */
    public function renew_cookie($cookie_lifetime = NULL)
    {
        // Make sure cookie is loaded
        if ( ! is_object($this->_cookie_model))
        {
            throw new Kohana_Exception('Cookie must be loaded to be renewed.');
        }

        // Set cookie lifetime
        $cookie_lifetime = $cookie_lifetime ?: $this->_default_cookie_lifetime;

        // Generate new secure key
        $secure_key = Text::random('alnum', 64);

        // Update the cookie
        Cookie::set(
            $this->_cookie_name,
            $secure_key,
            $cookie_lifetime
        );

        $this->_cookie_model
            ->set('secure_key', $secure_key)
            ->set('expires_on', date('Y-m-d H:i:s', time() + $cookie_lifetime))
            ->save();
    }

    /**
     * Delete the current cookie
     */
    public function delete_cookie()
    {
        // Make sure cookie is loaded
        if ( ! is_object($this->_cookie_model))
        {
            throw new Kohana_Exception('Cookie must be loaded to be deleted.');
        }

        // Delete cookie
        Cookie::delete($this->_cookie_name);

        // Delete cookie from database
        $this->_cookie_model->delete();
    }

    /**
     * Get the users id
     *
     * @return  int
     */
    public function user_id()
    {
        return ($this->_cookie_model)
            ? $this->_cookie_model->get('user_id')
            : NULL;
    }

    /**
     * Returns a singleton instance of the class.
     *
     * @return  User_Cookie
     */
    public static function instance()
    {
        if ( ! self::$_instance instanceof User_Cookie)
        {
            self::$_instance = new User_Cookie();
        }

        return self::$_instance;
    }
}

// END Kohana_User_Cookie
