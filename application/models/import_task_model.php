<?php

class Import_Task_Model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function create_task($file) {
        $data = array('file' => pathinfo($file, PATHINFO_BASENAME));
        $this->db_master->insert('lib_import_tasks', $data);
        return $this->db_master->insert_id();
    }

    function get_task($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('lib_import_tasks');
        $res = $query->result_array();
        return $res[0];
    }

    function update_task($id, $data = array()) {
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_import_tasks', $data);
        return $id;
    }

    function get_tasks($filter = array(), $limit = 0){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit) {
            $this->db->limit($limit);
        }
        $query = $this->db->get('lib_import_tasks');
        $res = $query->result_array();
        return $res;
    }
}