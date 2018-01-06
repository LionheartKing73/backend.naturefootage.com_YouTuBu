<?php

class BrowseListItems_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_browse_list_items_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_browse_list_items');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_browse_list_items');
    }

    function get_browse_list_items_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_browse_list_items');
        $res = $query->result_array();
        return $res;
    }

    function save_browse_list_item($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_browse_list_items', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_browse_list_items', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_browse_list_item($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_browse_list_items');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_browse_list_items($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_browse_list_items', array('id' => $id));
            }
        }
    }

    function change_visible($ids) {
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->query('UPDATE lib_browse_list_items SET status = !status where id = ' . $id);
            }
        }
    }
}