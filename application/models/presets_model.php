<?php

class Presets_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_presets_count() {
        return $this->db->count_all('lib_presets');
    }

    function get_presets_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_presets');
        $res = $query->result_array();
        return $res;
    }

    function save_preset($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_presets', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_presets', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_preset($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_presets');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_presets($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_presets', array('id' => $id));
            }
        }
    }
}