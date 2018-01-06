<?php
class Publication_model extends CI_Model {
  
  function Publication_model() {
      parent::__construct();
      $this->db_master = $this->load->database('master', TRUE);
  }

  #------------------------------------------------------------------------------------------------

  function get_pages_count($lang, $filter) {
    $query = $this->db->query('select lp.id from lib_pages as lp, lib_pages_content as lpc where lp.id=lpc.page_id and lpc.lang='.$this->db->escape($lang).$filter);
    return $query->num_rows();
  }

  #------------------------------------------------------------------------------------------------

  function get_pages_list($lang, $filter, $order, $limit) {
    $query = $this->db->query('select lp.*, lpc.title, DATE_FORMAT(lp.ctime, \'%d.%m.%Y %T\') as ctime from lib_pages as lp left join lib_pages_content as lpc on lp.id=lpc.page_id and lpc.lang='.$this->db->escape($lang).' where lp.id>0 '.$filter.$order.$limit);
    return $query->result_array();
  }

  #------------------------------------------------------------------------------------------------

  function change_visible($ids) {
    if(count($ids)) {
      foreach($ids as $id) {
        $this->db_master->query('UPDATE lib_pages set active = !active where id='.$id);
      }
    }
  }
  
  #------------------------------------------------------------------------------------------------
  
  function is_predefined($alias) {
  
      if(is_numeric($alias)) 
          $conditions = array('id'=>$alias);
      else
          $conditions = array('alias1'=>$alias);
      
      $result = false;
      
      $query = $this->db->get('lib_pages', $conditions, 1);
      
      if($query->num_rows() > 0) {
          $row = $query->row_array();
          $result = ($row['predefined'] == 1) ? true : false;
      }
      
      return $result; 
      
  }
  
  #------------------------------------------------------------------------------------------------

  function get_page_by_alias($alias, $lang) {
    $query = $this->db->query(
      'SELECT lp.alias1, lp.alias2, lpc.*
      FROM lib_pages lp
      LEFT JOIN lib_pages_content lpc ON lp.id=lpc.page_id AND lpc.lang='.$this->db->escape($lang).
      ' WHERE lp.alias1=\''.$alias.'\'');
    $row = $query->result_array();
    return $row[0];
  }
  
  #------------------------------------------------------------------------------------------------

  function delete_pages($ids) {
    if(count($ids)) {
      foreach($ids as $id) {
        if($this->is_predefined($id)) continue;  
        $this->db_master->delete('lib_pages', array('id'=>$id));
        $this->db_master->delete('lib_pages_content', array('page_id'=>$id));
      }
    }
  }

  #------------------------------------------------------------------------------------------------

  function get_page($id, $lang) {
    $query = $this->db->query(
      'SELECT lp.alias1, lp.alias2, lpc.*
      FROM lib_pages lp
      LEFT JOIN lib_pages_content lpc ON lp.id=lpc.page_id AND lpc.lang='.$this->db->escape($lang).
      ' WHERE lp.id='.intval($id));
    $row = $query->result_array();
    return $row[0];
  }

  #------------------------------------------------------------------------------------------------

  function save_page($id, $lang) {
    $data_page['alias1'] = $this->input->post('alias1');
    $data_page['alias2'] = $this->input->post('alias2');

    $data_content['page_id'] = $id;
    $data_content['lang'] = $lang;
    $data_content['title'] = $this->input->post('title');
    $data_content['body'] = $this->input->post('body');
    $data_content['meta_title'] = $this->input->post('meta_title');
    $data_content['meta_desc'] = $this->input->post('meta_desc');
    $data_content['meta_keys'] = $this->input->post('meta_keys');

    if($id) {
      $this->db_master->where('id', $id);
      $this->db_master->update('lib_pages', $data_page);

      $query = $this->db->get_where('lib_pages_content', array('page_id'=>$id,'lang'=>$lang));
      $row = $query->result_array();

      if(count($row)) {
        $this->db_master->where('id', $row[0]['id']);
        $this->db_master->update('lib_pages_content', $data_content);
      }
      else
        $this->db_master->insert('lib_pages_content', $data_content);
    }
    else {
      $data_page['ctime'] = date('Y-m-d H:i:s');
      $this->db_master->insert('lib_pages', $data_page);

      $data_content['page_id'] = $this->db_master->insert_id();
      $this->db_master->insert('lib_pages_content', $data_content);
    }
  }

  #------------------------------------------------------------------------------------------------

    function get_page_content($id, $lang) {
        if($this->uri->segment(2)=='publication' && $this->uri->segment(3)=='content') {
            $id = ($this->id) ? $this->id : 1;
            $query = $this->db->query(
                'SELECT lpc.* FROM lib_pages lp
        LEFT JOIN lib_pages_content lpc ON lp.id=lpc.page_id AND lpc.lang='.$this->db->escape($lang).
                    ' WHERE lp.id='.intval($id));
        }
        else {
            $uri = trim($_SERVER['REQUEST_URI'], '/');
            $parts = explode('/', $uri);
            if (in_array($parts[0], array_keys($this->config->item('support_languages')))) {
                unset($parts[0]);
            }
            $alias = implode('/', $parts);

            $query = $this->db->query(
                'SELECT lpc.* FROM lib_pages lp
        LEFT JOIN lib_pages_content lpc ON lp.id=lpc.page_id AND lpc.lang='.$this->db->escape($lang).
                    ' WHERE (lp.alias1='.$this->db->escape($alias).' OR lp.alias2='.$this->db->escape($alias)
                    .') AND lp.active=1');
        }

        if($query->num_rows()) {
            $row = $query->result_array();
            return $row[0];
        }
        else
            show_404();
    }



}
