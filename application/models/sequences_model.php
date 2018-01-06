<?php

class Sequences_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_sequences_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_sequences');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_sequences');
    }

    function get_sequences_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_sequences');
        $res = $query->result_array();
        return $res;
    }

    function save_sequence($id){
        $data = $this->input->post();
        unset($data['add_selected_clips']);
        if(isset($data['clips'])){
            $clips = explode(',', $data['clips']);
            unset($data['clips']);
        }
        else{
            $clips = array();
        }
        if($this->session->userdata('client_uid')) {
            $data['provider_id'] = $this->session->userdata('client_uid');
        }
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_sequences', $data);
        }
        else {
            $this->db_master->insert('lib_sequences', $data);
            $id = $this->db_master->insert_id();
        }
        if($clips)
            $this->add_items($id, $clips);
        return $id;
    }

    function get_sequence($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_sequences');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_sequences($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $check = $this->get_sequence($id);
                if ($check['provider_id'] === $this->session->userdata('client_uid')
                    || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']) {
                    $this->db_master->delete('lib_sequences', array('id' => $id));
                    $this->db_master->delete('lib_clip_sequences', array('sequence_id' => $id));
                }
            }
        }
    }

    function add_items($id, $items_ids){
        if($items_ids && is_array($items_ids)){
            foreach($items_ids as $item_id){
                $this->db_master->delete('lib_clip_sequences', array('sequence_id' => $id, 'clip_id' => $item_id));
                $this->db_master->insert('lib_clip_sequences', array('sequence_id' => $id, 'clip_id' => $item_id));
            }
        }
    }
}