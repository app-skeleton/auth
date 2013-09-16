<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_Auth_Exception extends Kohana_Exception {

    /**
     * Error codes
     */
    const E_INVALID_CREDENTIALS     = 31;
    const E_INACTIVE_ACCOUNT        = 32;
    const E_INVALID_COOKIE          = 33;
    const E_INVALID_RECOVERY_LINK   = 34;
    const E_UNAUTHENTICATED         = 35;
    const E_AUTHENTICATED           = 36;
    const E_RESOURCE_NOT_FOUND      = 11;

    /**
     * @var array   Default error messages
     */
    public static $default_error_messages = array(
        31  => 'Invalid login credentials.',
        32  => 'This account is inactive.',
        33  => 'This cookie is expired or invalid.',
        34  => 'This recovery link is expired on invalid.',
        35  => 'Unauthenticated users can not perform this action.',
        36  => 'Authenticated users can not perform this action.',
        11  => 'Can not find the given resource.'
    );

    /**
     * Construct
     *
     * @param   int         $code       The exception code
     * @param   string      $message    Error message
     * @param   array       $variables  Translation variables
     * @param   array       $data       Data associated with the exception
     */
    public function __construct($code, $message = NULL, array $variables = NULL, $data = NULL)
    {
        if ( ! isset($message) && isset(self::$default_error_messages[$code]))
        {
            $message = self::$default_error_messages[$code];
        }

        parent::__construct($message, $variables, $code, NULL, $data);
    }
}

// END Kohana_Auth_Exception
