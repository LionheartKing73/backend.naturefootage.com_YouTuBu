<?php

class Tokens_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_tokens_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_download_tokens');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_download_tokens');
    }

    function get_tokens($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_download_tokens');
        $res = $query->result_array();
        return $res;
    }

    function save_token($id){
        $data = $this->input->post();
        if($this->session->userdata('client_uid')) {
            $data['provider_id'] = $this->session->userdata('client_uid');
        }
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_download_tokens', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_download_tokens', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_token($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_download_tokens');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_tokens($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_download_tokens', array('id' => $id));
            }
        }
    }

    function change_visible($ids) {
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->query('UPDATE lib_download_tokens SET status = !status where id = ' . $id);
            }
        }
    }

    function disable_order_tokens($order_id){
        $this->db_master->where('order_id', $order_id);
        $this->db_master->update('lib_download_tokens', array('status' => 0));
    }

    function get_token_path($token){
        $this->db->select('path');
        $this->db->where('token', $token);
        $query = $this->db->get('lib_download_tokens');
        $res = $query->result_array();
        if($res[0]['path'])
            return $res[0]['path'];
        else
            return false;
    }
}