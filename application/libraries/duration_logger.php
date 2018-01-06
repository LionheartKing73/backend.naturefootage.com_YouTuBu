<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Duration_logger {

    private $db;
    private $_logtime = array();

    public function __construct() {
        $this->db = &get_instance()->load->database('master', TRUE);
    }

    public function start($marker) {
        $this->_logtime[$marker] = microtime(true);
    }

    public function save($marker, $method = '', array $additionalData = array()) {
        if (!isset($this->_logtime[$marker])) {
            // do nothing
            return false;
        }
        $duration = microtime(true) - $this->_logtime[$marker];
        unset($this->_logtime[$marker]);

        $query = sprintf(
            "INSERT INTO logtime (method, _POST, _GET, _REQUEST, addInfo, duration)
              VALUES (\"%s\", \"%s\",\"%s\", \"%s\", \"%s\", %f)
            ",
            $method,
            addslashes(print_r($_POST, true)),
            addslashes(print_r($_GET, true)),
            addslashes(print_r($_REQUEST, true)),
            addslashes(print_r($additionalData, true)),
            $duration
        );

        $this->db->query($query);
    }
}