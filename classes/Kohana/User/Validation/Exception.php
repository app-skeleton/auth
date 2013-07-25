<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Auth Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2013 Pap Tamas
 * @website		https://bitbucket.org/paptamas/kohana-auth
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_User_Validation_Exception extends Kohana_Exception {

    /**
     * Error list
     *
     * @var
     */
    public $_errors;

    /**
     * Construct a new exceptions
     *
     * @param   array   $errors
     * @param   string  $message
     * @param   array   $values
     * @param   int     $code
     * @return  Kohana_User_Validation_Exception
     *
     */
    public function __construct(array $errors, $message = '', array $values = NULL, $code = 0)
    {
        $this->_errors = $errors;
        parent::__construct($message, $values, $code);
    }

    /**
     * Return errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }
}

// END Kohana_User_Validation_Exception