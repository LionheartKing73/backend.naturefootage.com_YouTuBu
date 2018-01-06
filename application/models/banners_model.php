<?php

class Banners_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

  function get_banners_list() {
    $query = $this->db->query('SELECT * FROM lib_banners ORDER BY ord, id');
    return $query->result_array();
  }

  #------------------------------------------------------------------------------------------------

  function get_banner($id) {
    $query = $this->db->query('SELECT * FROM lib_banners WHERE id = ?', $id);
    $banner = $query->result_array();
    return $banner[0];
  }

  #------------------------------------------------------------------------------------------------

  function add_banner($data) {
    $data['ord'] = intval($data['ord']);
    $this->db_master->query('INSERT INTO lib_banners(name, type, resource, ord) VALUES(?, ?, ?, ?)',
      array($data['name'], $data['type'], $data['resource'], $data['ord']));
    return $this->db_master->insert_id();
  }

  #------------------------------------------------------------------------------------------------

  function update_banner($data) {
    $data['ord'] = intval($data['ord']);
    $this->db_master->query('UPDATE lib_banners SET name=?, type=?, resource=?, ord=? WHERE id=?',
      array($data['name'], $data['type'], $data['resource'], $data['ord'], $data['id']));
  }

  #------------------------------------------------------------------------------------------------

    function get_playlist() {
        $sort = $this->get_banners_sort();
        $order_by = $sort == 'number' ? 'ord' : 'RAND()';
        $list = $this->db->query('SELECT resource FROM lib_banners WHERE active=1 ORDER BY '
            . $order_by)->result_array();

        $mime_types = array('mp4' => 'video/mp4', 'm4v' => 'video/x-m4v', 'flv' => 'video/x-flv', 'f4v' => 'video/f4v');

        foreach ($list as &$item) {
            $file_ext = strtolower($this->api->get_file_ext($item['resource']));
            $item['mime_type'] = $mime_types[$file_ext];
        }
        return $list;
    }




    #------------------------------------------------------------------------------------------------

  function change_visible($ids) {
    if(count($ids)) {
      foreach($ids as $id) {
        $this->db_master->query('UPDATE lib_banners SET active = !active WHERE id='.$id);
      }
    }
  }

  #------------------------------------------------------------------------------------------------

  function get_banners_sort() {
    $query = $this->db->query("SELECT value FROM lib_settings WHERE name='banners_sort'");
    $banners_sort = $query->result_array();
    return $banners_sort[0]['value'];
  }

  #------------------------------------------------------------------------------------------------

  function set_banners_sort($value) {
    $this->db_master->query("UPDATE lib_settings SET value = ? WHERE name = 'banners_sort'",
      array($value));
  }

  #------------------------------------------------------------------------------------------------

  function delete_banner($ids) {
    $ids = implode(', ', $ids);
    $this->db_master->query('DELETE FROM lib_banners WHERE id IN (' . $ids . ')');
  }
}
