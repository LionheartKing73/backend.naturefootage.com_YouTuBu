<?php

class Discount_display_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_discount_displays_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_discount_displays');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_discount_displays');
    }

    function get_rf_discount_displays_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_discount_displays_rf');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_discount_displays_rf');
    }

    function get_discount_displays_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_discount_displays');
        $res = $query->result_array();
        return $res;
    }

    function get_rf_discount_displays_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_discount_displays_rf');
        $res = $query->result_array();
        return $res;
    }

    function save_discount_display($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_discount_displays', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_discount_displays', $data);
            return $this->db_master->insert_id();
        }
    }

    function save_rf_discount_display($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_discount_displays_rf', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_discount_displays_rf', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_discount_display($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_discount_displays');
        $res = $query->result_array();
        return $res[0];
    }

    function get_rf_discount_display($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_discount_displays_rf');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_discount_displays($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_discount_displays', array('id' => $id));
            }
        }
    }

    function delete_rf_discount_displays($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_discount_displays_rf', array('id' => $id));
            }
        }
    }

    function get_types_list(){
        $this->db->select('type');
        $this->db->distinct();
        $query = $this->db->get('lib_discount_displays');
        $res = $query->result_array();
        $types = array();
        foreach($res as $item) {
            $types[] = $item['type'];
        }
        return $types;
    }

    function get_rf_types_list(){
        $this->db->select('type');
        $this->db->distinct();
        $query = $this->db->get('lib_discount_displays_rf');
        $res = $query->result_array();
        $types = array();
        foreach($res as $item) {
            $types[] = $item['type'];
        }
        return $types;
    }
}