<?php

class Clipsettings_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

  function get_list($lang='en', $used_only=false) {
    $sql = $used_only ?
      'SELECT DISTINCT cs.id, csc.name
      FROM lib_clipsettings cs
      LEFT JOIN lib_clipsettings_content csc ON csc.setting_id = cs.id AND csc.lang=?
      INNER JOIN lib_clips c ON c.setting_id = cs.id AND c.active = 1      
      ORDER BY csc.name'
      :
      'SELECT DISTINCT cs.id, csc.name
       FROM lib_clipsettings cs
       LEFT JOIN lib_clipsettings_content csc ON csc.setting_id = cs.id AND csc.lang=?
       ORDER BY csc.name';
    return $this->db->query($sql, $lang)->result_array();
  }
#---------------------------------------------------------------------------------------------------
  function update($id, $name) {
    $this->db_master->query(
      'UPDATE lib_clipsettings_content
       SET name=?
       WHERE id=?', array($name, $id));
  }
#---------------------------------------------------------------------------------------------------
  function insert($name, $lang='en') {
    $this->db_master->query('INSERT INTO lib_clipsettings VALUES()');
    $sid = $this->db_master->insert_id();
    $this->db_master->insert('lib_clipsettings_content', array('setting_id'=>$sid, 'lang'=>$lang, 'name'=>$name));
  }
#---------------------------------------------------------------------------------------------------
  function delete($id) {
    $this->db_master->delete('lib_clipsettings_content', array('setting_id'=>$id));
    $this->db_master->delete('lib_clipsettings', array('id'=>$id));
  }
}
