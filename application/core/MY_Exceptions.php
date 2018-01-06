<?php

/**
 * Extends CI_Exceptions to send mails to detect write operations to read only db
 *
 * @author nikita.bunenkov
 */
class MY_Exceptions extends CI_Exceptions
{
    /**
     * DO NOT log next severenity level
     *
     * Unfortunately there are lots of E_NOTICE errors, no way to handle all of them
     *
     * @var array
     */
    private $ignoreSeverenityLevels = [E_NOTICE, E_DEPRECATED];

    /**
     * Exception Logger
     *
     * This function logs PHP generated error messages
     *
     * log any, except $ignoreSeverenityLevels errors
     *
     * @access	private
     * @param	string	the error severity
     * @param	string	the error string
     * @param	string	the error filepath
     * @param	string	the error line number
     * @return	string
     */
    function log_exception($severity, $message, $filepath, $line)
    {
        if (!in_array($severity, $this->ignoreSeverenityLevels)) {
            parent::log_exception($severity, $message, $filepath, $line);
        }
    }
}