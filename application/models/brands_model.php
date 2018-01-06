<?php

class Brands_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_brands_count() {
        return $this->db->count_all('lib_brands');
    }

    function get_brands_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                if (is_array($value)) {
                    $this->db->where_in($param, $value);
                }
                else {
                    $this->db->where($param, $value);
                }
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_brands');
        $res = $query->result_array();
        return $res;
    }

    function save_brand($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_brands', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_brands', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_brand($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_brands');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_brands($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_brands', array('id' => $id));
            }
        }
    }

}