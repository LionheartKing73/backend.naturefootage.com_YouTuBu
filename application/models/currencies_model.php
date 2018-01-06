<?php
class Currencies_model extends CI_Model {

  function Currencies_model() {
      parent::__construct();
      $this->db_master = $this->load->database('master', TRUE);
  }

  #------------------------------------------------------------------------------------------------

  function get_currencies($filter=null) {
    $query = $this->db->query('select lc.* from lib_currencies as lc where lc.id>0 '.$filter);
    return $query->result_array();
  }

  #------------------------------------------------------------------------------------------------

  function get_currency($id) {
    $query = $this->db->query('select * from lib_currencies where id='.intval($id));
    $rows = $query->result_array();
    return $rows[0];
  }

  #------------------------------------------------------------------------------------------------

  function get_currency_name($code) {
    $query = $this->db->query('select * from lib_currencies where code='.$this->db->escape($code));
    $rows = $query->result_array();
    return $rows[0]['title'];
  }

  #------------------------------------------------------------------------------------------------

  function save_currency($id) {
    $data['title'] = $this->input->post('title');
    $data['code'] = $this->input->post('code');
    $data['rate'] = $this->input->post('rate');
    $data['is_default'] = $this->input->post('is_default');

    if($id) {
      $this->db_master->where('id', $id);
      $this->db_master->update('lib_currencies', $data);
    }
    else
      $this->db_master->insert('lib_currencies', $data);
  }

  #------------------------------------------------------------------------------------------------

  function change_visible($ids) {
    if(count($ids)) {
      foreach($ids as $id) {
        $this->db_master->query('UPDATE lib_currencies set active = !active where id='.$id);
      }
    }
  }

  #------------------------------------------------------------------------------------------------

  function delete_currencies($ids) {
    if(count($ids)) {
      foreach($ids as $id) {
        $this->db_master->delete('lib_currencies', array('id'=>$id));
      }
    }
  }

  #------------------------------------------------------------------------------------------------

  function check_default() {
    $query = $this->db->query('select * from lib_currencies where is_default=1');
    return $query->num_rows();
  }

  #------------------------------------------------------------------------------------------------

  function get_default() {
    $query = $this->db->query('select * from lib_currencies where is_default=1');
    $rows = $query->result_array();
    return $rows[0];
  }

  #------------------------------------------------------------------------------------------------

  function get_country_currency($country_code='') {
    if (!$country_code) {
      $country_code = $_SERVER['GEOIP_COUNTRY_CODE'];
    }

    if (!$country_code) {
      return $this->get_default();
    }

    $row = $this->db->query('SELECT cu.code, cu.rate FROM lib_currencies cu
        INNER JOIN lib_countries co ON co.currency = cu.code
        WHERE co.code = ?', array($country_code))->result_array();
    return $row[0];
  }
  
  #------------------------------------------------------------------------------------------------
  
  function get_current_currency() {
    $currency = $this->session->userdata('currency');
    if(!$currency['code'] || !$currency['rate']) {
      $currency = $this->get_country_currency();
    }
    return $currency;
  }
}
