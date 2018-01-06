<?php

/**
 * Class MY_Log
 *
 * @author nikita.bunenkov
 */
class MY_Log extends CI_Log
{

    /**
     * email, emails or false if not email log enabled
     * @var bool|string|array
     */
    private $logEmails = false;

    /**
     * 404 email flag
     *
     * @var bool
     */
    private $email404 = false;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $config =& get_config();

        if (isset($config['log_emails']) && (is_array($config['log_emails']) || is_string($config['log_emails']))) {
            $this->logEmails = is_array($config['log_emails']) 
                ? implode(',', $config['log_emails']) 
                : $config['log_emails'];
        }

        if (isset($config['log_email_404'])) {
            $this->email404 = (bool) $config['log_email_404'];
        }
    }

    /**
     * determines should logs been emailed, if this is false, no metter what value contains config item log_emails
     *
     * @return bool
     */
    public function shouldEmailLog()
    {
        return true;
    }

    /**
     * determine if 404 status should be emailed
     *
     * @return bool
     */
    public function shouldEmail404()
    {
        return $this->email404;
    }

    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @param	string	the error level
     * @param	string	the error message
     * @param	bool	whether the error is a native PHP error
     * @return	bool
     */
    public function write_log($level = 'error', $msg, $php_error = FALSE)
    {
        $logResult = parent::write_log($level, $msg, $php_error);

        if ($this->shouldEmailLog()) {
            $logResult &= $this->emailLog($level, $msg, $php_error);
        }

        return $logResult;

    }

    /**
     * Log to email
     *
     * @param string $level
     * @param $msg
     * @param bool $php_error
     *
     * @return bool
     */
    private function emailLog($level = 'error', $msg, $php_error = FALSE)
    {
        if (!$this->logEmails) {
            // no emails, no party
            return false;
        }

        if (!$this->shouldEmail404() && strpos($msg, '404') === 0) {
            // no need to email 404 errors
            return false;
        }

        if ( !$this->checkLogLevel($level)) {
            return false;
        }

        // prepare message from $msg and trace
        $message = $this->prepareMessage($level, $msg);
        // get globals data
        $globals = $this->getGlobals();

        if (ENVIRONMENT == 'development') {
            // just print on development
            echo '<pre>';
            print_r($message);
            print_r($globals);
            echo '</pre>';
        } else {
            // mail on production
//            mail(
//                $this->logEmails,
//                'Naturefootage backend ' . strtoupper(ENVIRONMENT) . ' error: '. $msg,
//                implode(PHP_EOL, $message) . PHP_EOL . print_r($globals, true)
//            );
        }

        return true;
    }

    /**
     * check that log level is enough for loging
     *
     * @param $level
     *
     * @return bool
     */
    private function checkLogLevel($level)
    {
        $level = strtoupper($level);
        return isset($this->_levels[$level]) && ($this->_levels[$level] <= $this->_threshold);
    }

    /**
     * @param string $level
     * @param string $msg
     *
     * @return array
     */
    private function prepareMessage($level, $msg)
    {
        // prepare message from $msg and trace
        $message = [];

        $message[] = $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";

        $trace = debug_backtrace();

        foreach ($trace as $call) {
            if (isset($call['class']) && $call['class'] == __CLASS__) {
                // skip this class referense
                continue;
            }
            if (isset($call['file'])) {
                // Found it - use a relative path for safety
                $message[] = 'Filename: ' . $call['file'] . '; Line Number: '.$call['line'];
            }
        }

        return $message;
    }

    /**
     * get globals info
     *
     * returns array with $_GET, $_POST, $_SESSION array data
     * @return array
     */
    private function getGlobals()
    {
        return [
            '_GET' => print_r($_GET, true),
            '_POST' => print_r($_POST, true),
            '_SESSION' => print_r($_SESSION, true),
        ];
    }

}
