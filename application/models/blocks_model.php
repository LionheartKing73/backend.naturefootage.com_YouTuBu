<?php
class Blocks_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }
  
  function get($id, $lang='en') {
    $row = $this->db->query('SELECT * FROM lib_blocks WHERE id = ? AND lang = ?',
      array($id, $lang))->result_array();
    return $row[0];
  }

  #------------------------------------------------------------------------------------------------

  function save($id, $lang='en') {
    $this->db_master->update('lib_blocks', array('content'=>$this->input->post('content')),
      array('id'=>$id, 'lang'=>$lang));
  }

}
