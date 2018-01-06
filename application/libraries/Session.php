<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package        CodeIgniter
 * @author        Dariusz Debowczyk
 * @copyright    Copyright (c) 2006, D.Debowczyk
 * @license        http://www.codeignitor.com/user_guide/license.html
 * @link        http://www.codeigniter.com
 * @since        Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Session class using native PHP session features and hardened against session fixation.
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Sessions
 * @author        Dariusz Debowczyk
 * @link        http://www.codeigniter.com/user_guide/libraries/sessions.html
 */
class CI_Session {
    var $session_id_ttl = 600; // session id time to live (TTL) in seconds
    var $flash_key = 'flash'; // prefix for "flash" variables (eg. flash:new:message)
    var $userdata = array();

    function __construct()
    {
        log_message('debug', "Native_session Class Initialized");

        if (config_item('sess_use_database') === true && config_item('sess_table_name')) {
            $this->_initDBSessionHanler(config_item('sess_table_name'), config_item('sess_expiration'));
        }
        $this->_sess_run();
    }

    /**
     * Regenerates session id
     */
    function regenerate_id()
    {
//        if ($_SERVER['environment'] == 'staging') {
//            // temporary measure
//            return;
//        }
        // copy old session data, including its id
        $old_session_id = session_id();
        $old_session_data = $_SESSION;

        // regenerate session id and store it
        session_regenerate_id();
        $new_session_id = session_id();

        // switch to the old session and destroy its storage
        session_id($old_session_id);
        session_destroy();

        // switch back to the new session id and send the cookie
        session_id($new_session_id);
        session_start();

        // restore the old session data into the new session
        $_SESSION = $old_session_data;

        // update the session creation time
        $this->setSessionGeneratedTime();

        // session_write_close() patch based on this thread
        // http://www.codeigniter.com/forums/viewthread/1624/
        // there is a question mark ?? as to side affects

        // end the current session and store session data.
        session_write_close();
    }

    /**
     * Destroys the session and erases session storage
     */
    function destroy()
    {
        //unset($_SESSION);
        session_unset();
        if ( isset( $_COOKIE[session_name()] ) )
        {
            setcookie(session_name(), '', time()-42000, '/');
        }
        session_destroy();
    }

    function sess_destroy(){
        $this->destroy();
    }

    /**
     * Reads given session attribute value
     */
    function userdata($item)
    {
        if($item == 'session_id'){ //added for backward-compatibility
            return session_id();
        }else{
            return ( ! isset($_SESSION[$item])) ? false : $_SESSION[$item];
        }
    }

    /**
     * Sets session attributes to the given values
     */
    function set_userdata($newdata = array(), $newval = '')
    {
        if (is_string($newdata))
        {
            $newdata = array($newdata => $newval);
        }

        if (count($newdata) > 0)
        {
            foreach ($newdata as $key => $val)
            {
                $_SESSION[$key] = $val;
            }
        }
    }

    /**
     * Erases given session attributes
     */
    function unset_userdata($newdata = array())
    {
        if (is_string($newdata))
        {
            $newdata = array($newdata => '');
        }

        if (count($newdata) > 0)
        {
            foreach ($newdata as $key => $val)
            {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
     * Starts up the session system for current request
     */
    function _sess_run()
    {
        //header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
        session_start();
        $this->userdata = $_SESSION;

//        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') && !strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
//            if (count($_COOKIE) === 0) {
//                echo '<script>top.location = "http://fsearch.com/startsession.php";</script>';
//            }
//        }

        // check if session id needs regeneration
        if ( $this->_session_id_expired() )
        {
            // regenerate session id (session data stays the
            // same, but old session storage is destroyed)
            $this->regenerate_id();
        }

        // delete old flashdata (from last request)
        $this->_flashdata_sweep();

        // mark all new flashdata as old (data will be deleted before next request)
        $this->_flashdata_mark();
    }

    /**
     * Checks if session has expired
     */
    function _session_id_expired()
    {
        return false;

        if (!$this->getSessionGeneratedTime()) {
            $this->setSessionGeneratedTime();
            return false;
        }


        $expireTime = $this->getSessionGeneratedTime() + $this->session_id_ttl;
        $time = time();

        return $expireTime <  $time;
    }

    /**
     * Sets "flash" data which will be available only in next request (then it will
     * be deleted from session). You can use it to implement "Save succeeded" messages
     * after redirect.
     */
    function set_flashdata($key, $value)
    {
        $flash_key = $this->flash_key.':new:'.$key;
        $this->set_userdata($flash_key, $value);
    }

    /**
     * Keeps existing "flash" data available to next request.
     */
    function keep_flashdata($key)
    {
        $old_flash_key = $this->flash_key.':old:'.$key;
        $value = $this->userdata($old_flash_key);

        $new_flash_key = $this->flash_key.':new:'.$key;
        $this->set_userdata($new_flash_key, $value);
    }

    /**
     * Returns "flash" data for the given key.
     */
    function flashdata($key)
    {
        $flash_key = $this->flash_key.':old:'.$key;
        return $this->userdata($flash_key);
    }

    /**
     * PRIVATE: Internal method - marks "flash" session attributes as 'old'
     */
    function _flashdata_mark()
    {
        foreach ($_SESSION as $name => $value)
        {
            $parts = explode(':new:', $name);
            if (is_array($parts) && count($parts) == 2)
            {
                $new_name = $this->flash_key.':old:'.$parts[1];
                $this->set_userdata($new_name, $value);
                $this->unset_userdata($name);
            }
        }
    }

    /**
     * PRIVATE: Internal method - removes "flash" session marked as 'old'
     */
    function _flashdata_sweep()
    {
        foreach ($_SESSION as $name => $value)
        {
            $parts = explode(':old:', $name);
            if (is_array($parts) && count($parts) == 2 && $parts[0] == $this->flash_key)
            {
                $this->unset_userdata($name);
            }
        }
    }

    /**
     * set session handler to custom
     *
     * @param string $tableName
     */
    private function _initDBSessionHanler($tableName, $expirationTime) {
        // load write db instance
        $db = & load_class('Loader', 'core')->database('master', true);
        // load read db connection
        $readDb = & load_class('Loader', 'core')->database('default', true);
        
        // instantiate db session handler
        $sessionHandler = & load_class('DBSessionHandler');
        $sessionHandler->setDbConnection($db);
        $sessionHandler->setReadDbConnection($readDb);
        $sessionHandler->setDbTable($tableName);
        if (is_int($expirationTime) && $expirationTime > 0) {
            $sessionHandler->setExpirationTime($expirationTime);
        }

        // make it to be a session handler
        session_set_save_handler($sessionHandler, true);
    }

    /**
     * get SESSION GENERATION time in unix timestamp format, 0 of time is unknown
     *
     * @return int
     */
    private function getSessionGeneratedTime()
    {
        return isset($_SESSION['generation_time']) ? $_SESSION['generation_time'] : 0;
    }

    /**
     * @param int|null $time, time in unix timestamp format, if null, time() value will be used
     *
     * @return int
     */
    private function setSessionGeneratedTime($time = null)
    {
        if (is_null($time)) {
            $time = time();
        }

        return $_SESSION['generation_time'] = $time;
    }
}
?>