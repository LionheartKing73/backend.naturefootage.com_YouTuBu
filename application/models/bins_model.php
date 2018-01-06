<?php

class Bins_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_bins_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_bins');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_bins');
    }

    function get_bins_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_bins');
        $res = $query->result_array();
        return $res;
    }

    function save_bin($id){
        $data = $this->input->post();
        if($this->session->userdata('client_uid')) {
            $data['provider_id'] = $this->session->userdata('client_uid');
        }
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_bins', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_bins', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_bin($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_bins');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_bins($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $check = $this->get_bin($id);
                if ($check['provider_id'] === $this->session->userdata('client_uid')
                    || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']) {
                    $this->db_master->delete('lib_bins', array('id' => $id));
                    $this->db_master->delete('lib_clip_bins', array('bin_id' => $id));
                }
            }
        }
    }

    function add_items($id, $items_ids){
        if($items_ids && is_array($items_ids)){
            foreach($items_ids as $item_id){
                $this->db_master->delete('lib_clip_bins', array('bin_id' => $id, 'clip_id' => $item_id));
                $this->db_master->insert('lib_clip_bins', array('bin_id' => $id, 'clip_id' => $item_id));
            }
        }
    }
}