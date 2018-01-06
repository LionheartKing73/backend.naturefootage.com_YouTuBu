<?php
class Features_model extends CI_Model {

  function Features_model() {
      parent::__construct();
      $this->db_master = $this->load->database('master', TRUE);
    //$this->load->model('cats_model', 'cm');
    $this->load->model('clips_model', 'clm');
  }
  
  #------------------------------------------------------------------------------------------------

  function get_list($lang='en', $limit='', $active_only=false) {
    if ($limit) {
      $limit = ' LIMIT ' . $limit;
    }
    $clause = $active_only ? ' WHERE active = 1 ' : '';
    $list = $this->db->query('SELECT * FROM lib_features ' . $clause . ' ORDER BY ord, id'
      . $limit)->result_array();
    if ($list) {
      foreach ($list as &$item) {
          $item['image'] = $this->get_image($item);
      }
    }
    return $list;
  }

  #------------------------------------------------------------------------------------------------
  
  function get_image($item) {
    $image = '';
      if ($item['code']) {
          $image = $this->clm->get_thumb_by_code($item['code']);
      }
      elseif($item['resource']) {
      $image = $this->config->item('features_path') . $item['id'] . '.' . $item['resource'];
    }

    return $image;
  }
  
  #------------------------------------------------------------------------------------------------

  function get($id) {
    $feature = $this->db->query('SELECT * FROM lib_features WHERE id = ?', $id)->result_array();
    return $feature[0];
  }

  #------------------------------------------------------------------------------------------------

  function add($data) {
    unset($data['save'], $data['remove_image']);
    $data['type'] = intval($data['type']);
    $data['ctime'] = strftime('%Y-%m-%d %H-%M-%S');
    $data['ord'] = intval($data['ord']);
    $this->db_master->insert('lib_features', $data);
    return $this->db_master->insert_id();
  }

  #------------------------------------------------------------------------------------------------

  function update($data) {
    $remove_image = $data['remove_image'];
    unset($data['save'], $data['remove_image']);
    $data['ord'] = intval($data['ord']);
    if ($remove_image) {
      $data['resource'] = '';
    }
    $this->db_master->update('lib_features', $data, array('id'=>$data['id']));
  }

  #------------------------------------------------------------------------------------------------

  function change_visible($ids) {
    if(count($ids)) {
      foreach($ids as $id) {
        $this->db_master->query('UPDATE lib_features SET active = !active WHERE id='.$id);
      }
    }
  }

  #------------------------------------------------------------------------------------------------

  function delete($ids) {
    $ids = implode(', ', $ids);
    $this->db_master->query('DELETE FROM lib_features WHERE id IN (' . $ids . ')');
  }
  
}