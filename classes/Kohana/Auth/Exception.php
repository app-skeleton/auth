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
    const E_RESOURCE_NOT_FOUND      = 11;

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
        parent::__construct($message, $variables, $code, NULL, $data);
    }
}

// END Kohana_Auth_Exception