<?php

class Rm_model extends CI_Model {

  function Rm_model() {
      parent::__construct();
      $this->db_master = $this->load->database('master', TRUE);
  }

  #------------------------------------------------------------------------------------------------

  function save_rm($sets) {
    foreach($sets as $k=>$val) {
      $data['value'] = $val;

      $this->db_master->where('id', $k);
      $this->db_master->update('lib_rm', $data);
    }
  }

  #------------------------------------------------------------------------------------------------

  function get_rm($values=null) {
    $query = $this->db->query('select * from lib_rm order by type');
    $rows = $query->result_array();

    foreach($rows as $row) {
      if($values) $data[$row['id']] = $row['value'];
      else $data[$row['type']][] = $row;
    }

    return $data;
  }

  #------------------------------------------------------------------------------------------------

  function get_rm_coef() {
    $rows = $this->db->query('SELECT id, value FROM lib_rm')->result_array();

    $data = array();
    foreach($rows as $row) {
      $data[$row['id']] = $row['value'];
    }

    return $data;
  }
}
