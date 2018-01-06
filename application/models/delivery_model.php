<?php

class Delivery_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

  function get_list($rate=1) {
    $query = $this->db->query('SELECT * FROM lib_delivery ORDER BY ord, id');
    $list = $query->result_array();
    
    if ($rate != 1) {
      foreach ($list as &$item) {
        $item['cost'] *= $rate;
      }
    }

    return $list;
  }

  #------------------------------------------------------------------------------------------------

  function update($id) {
    $data = $_POST;
    unset($data['save']);
    $data['ord'] = intval($data['ord']);
    $data['cost'] = number_format(floatval(str_replace(',', '.', $data['cost'])), 2, '.', '');
    $this->db_master->where('id', $id);
    $this->db_master->update('lib_delivery', $data);
  }

  #------------------------------------------------------------------------------------------------
  
  function add() {
    $data = $_POST;
    unset($data['save']);
    $data['ord'] = intval($data['ord']);
    $data['cost'] = number_format(floatval(str_replace(',', '.', $data['cost'])), 2, '.', '');
    $this->db_master->insert('lib_delivery', $data);
    return $this->db_master->insert_id();
  }
  
  #------------------------------------------------------------------------------------------------

  function get($id) {
    $query = $this->db->query('SELECT * FROM lib_delivery WHERE id = ?', $id);
    $row = $query->result_array();
    return $row[0];
  }
  
  #------------------------------------------------------------------------------------------------

  function delete($ids) {
    $ids = implode(', ', $ids);
    $this->db_master->query('DELETE FROM lib_delivery WHERE id IN (' . $ids . ')');
  }
}
