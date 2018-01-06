<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Simple_Logger
{

    const LOG_ONLY_STAGE = true;

    private $_log = array(
        'login' => '__log_login'
    );

    public function __construct($db)
    {
        if (is_array($db)) {
            $this->db = array_shift($db);
        } elseif(is_object($db)) {
            $this->db = $db;
        } else {
            $this->db = &get_instance()->load->database('master', TRUE);
        }
    }

    public function save($marker, $state = '', $user = "")
    {
        if (!isset($this->_log[$marker])) {
            // do nothing
            return false;
        }

        if (self::LOG_ONLY_STAGE && $_SERVER['environment'] !== 'staging') {
            // do nothing
            return false;
        }

        $user = $user ?: $this->getuser();

        $query = sprintf(
            "INSERT INTO {$this->_log[$marker]} (state, user, _POST, _GET, _REQUEST, _COOKIE, _SESSION, _SERVER)
                VALUES (\"%s\", \"%s\",\"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\")",
            $state,
            $user,
            mysql_real_escape_string(print_r($_POST, true)),
            mysql_real_escape_string(print_r($_GET, true)),
            mysql_real_escape_string(print_r($_REQUEST, true)),
            mysql_real_escape_string(print_r($_COOKIE, true)),
            mysql_real_escape_string(print_r($_SESSION, true)),
            mysql_real_escape_string(print_r($_SERVER, true))
        );

        $this->db->query($query);

    }

    private function getuser()
    {
        $user = "";
        if (isset($_SESSION['login'])) {
            $user = $_SESSION['login'];
        } elseif (isset($_POST['login'])) {
            $user = $_POST['login'];
        } elseif (isset($_REQUEST['login'])) {
            $user = $_REQUEST['login'];
        }

        return $user;
    }
}