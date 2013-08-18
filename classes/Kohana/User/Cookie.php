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

    // Singleton instance
    protected static $_instance;

    // Config object
    protected $_config;

    // The cookie model instance
    protected $_cookie;

    // Error constants
    const ERROR_NONE = 0;
    const ERROR_COOKIE_EMPTY = 1;
    const ERROR_COOKIE_INVALID = 2;

    /**
     * Construct
     */
    protected function __construct()
    {
        $this->_config = Kohana::$config->load('auth/cookie');
    }

    /**
     * Try to load the cookie
     *
     * @throws  User_Cookie_Exception
     */
    public function load()
    {
        // Get cookie name from config
        $cookie_name = $this->_config['name'];

        // Check if cookie exists
        if (($cookie = Cookie::get($cookie_name)) !== NULL)
        {
            // Get cookie parts
            $exp = explode('|', $cookie);

            if (sizeof($exp) != 2)
            {
                // Cookie is invalid (delete it)
                Cookie::delete($cookie_name);

                // Throw Exception
                throw new User_Cookie_Exception('Invalid cookie.', array(), self::ERROR_COOKIE_INVALID);
            }
            else
            {
                // Check if the cookie exists in db
                $db_cookie = ORM::factory('User_Cookie')
                    ->where('user_id', '=', $exp[0])
                    ->and_where('random_key', '=', $exp[1])
                    ->find();

                if ( ! $db_cookie->loaded())
                {
                    // Cookie doesn't exists in db (it is invalid, let's delete it)
                    Cookie::delete($cookie_name);

                    // Throw Exception
                    throw new User_Cookie_Exception('Invalid cookie.', array(), self::ERROR_COOKIE_INVALID);
                }

                // Check if the cookie is outdated
                if (strtotime($db_cookie->expires_on) < time())
                {
                    // Delete the outdated cookie
                    Cookie::delete($cookie_name);

                    // Delete the outdated cookie from db also
                    $db_cookie->delete();

                    // Throw Exception
                    throw new User_Cookie_Exception('Invalid cookie.', array(), self::ERROR_COOKIE_INVALID);
                }

                // A valid cookie was found in db
                $this->_cookie = $db_cookie;
            }
        }
        else
        {
            // Throw Exception
            throw new User_Cookie_Exception('Empty cookie.', array(), self::ERROR_COOKIE_EMPTY);
        }
    }

    /**
     * Generate a new random key
     *
     * @return  string
     */
    public function generate()
    {
        return Text::random('alnum', 64);
    }

    /**
     * Create a new cookie
     *
     * @param   int $user_id
     * @param   int $cookie_lifetime
     */
    public function create($user_id, $cookie_lifetime = NULL)
    {
        // Get cookie name from config
        $cookie_name = $this->_config['name'];

        // Generate new random key
        $random_key = $this->generate();

        // Create cookie
        Cookie::set(
            $cookie_name,
            $user_id.'|'.$random_key,
            $this->cookie_lifetime($cookie_lifetime)
        );

        // Save the new cookie to database
        $this->_cookie = ORM::factory('User_Cookie');

        $this->_cookie->user_id = $user_id;
        $this->_cookie->random_key = $random_key;
        $this->_cookie->expires_on = date('Y-m-d H:i:s', time() + $this->cookie_lifetime($cookie_lifetime));

        $this->_cookie->save();
    }

    /**
     * Extend a cookie's lifetime
     *
     * @param   int $cookie_lifetime
     */
    public function extend($cookie_lifetime = NULL)
    {
        // The cookie must be loaded
        if ($this->_cookie)
        {
            // Get cookie name from config
            $cookie_name = $this->_config['name'];

            // Generate new random key
            $random_key = $this->generate();

            // Create cookie
            Cookie::set(
                $cookie_name,
                $this->_cookie->user_id.'|'.$random_key,
                $this->cookie_lifetime($cookie_lifetime)
            );

            // Save the new cookie to database
            $this->_cookie->random_key = $random_key;
            $this->_cookie->expires_on = date('Y-m-d H:i:s', time() + $this->cookie_lifetime($cookie_lifetime));

            $this->_cookie->save();
        }
    }

    /**
     * Delete the current cookie (if exists)
     */
    public function delete()
    {
        // The cookie must be loaded
        if ($this->_cookie)
        {
            // Get cookie name from config
            $cookie_name = $this->_config['name'];

            // Delete cookie
            Cookie::delete($cookie_name);

            // Delete cookie from database
            $this->_cookie->delete();
        }
    }

    /**
     * Get the users id
     *
     * @return  int
     */
    public function user_id()
    {
        return ($this->_cookie) ? $this->_cookie->user_id: NULL;
    }

    /**
     * Return the cookie lifetime
     *
     * @param   int $lifetime
     * @return  int
     */
    public function cookie_lifetime($lifetime = NULL)
    {
        return (is_numeric($lifetime)) ? $lifetime : $this->_config['lifetime'];
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
