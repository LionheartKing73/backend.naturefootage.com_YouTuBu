<?php

class Prices_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

  function get_prices_list() {
    $query = $this->db->query('SELECT * FROM lib_prices ORDER BY code');
    return $query->result_array();
  }

  #------------------------------------------------------------------------------------------------

  function get_price($id) {
    $row = $this->db->query('SELECT * FROM lib_prices WHERE id = ?', array($id))->result_array();
    return $row[0];
  }
  
  #------------------------------------------------------------------------------------------------
  
  var $prices;
  
  function get_price_id($code) {
    if (!$this->prices) {
      $list = $this->db->query('SELECT id, code FROM lib_prices')->result_array();
      foreach ($list as $row) {
        $this->prices[$row['code']] = $row['id'];
      }
    }
    
    return intval($prices[$code]);
  }

  #------------------------------------------------------------------------------------------------

  function save_price() {
    $id = $this->input->post('id');
    $data['code'] = $this->input->post('code');
    $data['price'] = number_format(floatval(str_replace(',', '.', $this->input->post('price'))), 2, '.', '');

    if ($id) {
      $this->db_master->where('id', $id);
      $this->db_master->update('lib_prices', $data);
    } else {
      $this->db_master->insert('lib_prices', $data);
    }
  }
  
  #------------------------------------------------------------------------------------------------

  function delete_prices($ids) {
    if(count($ids)){
      foreach($ids as $id){
        $this->db_master->delete('lib_prices', array('id'=>$id));
      }
    }
  }
}
