<?php

class Frontends_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_frontends_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_frontends');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_frontends');
    }

    function get_frontends_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_frontends');
        $res = $query->result_array();
        return $res;
    }

    function get_frontends_list_with_providers($filter = array()){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        $this->db->select('f.*, u.fname, u.lname, u.email, u.login, u.password');
        $this->db->from('lib_frontends f');
        $this->db->join('lib_users u', 'f.provider_id = u.id');
        $query = $this->db->get();
        $res = $query->result_array();
        return $res;
    }

    function save_frontend($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_frontends', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_frontends', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_frontend($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_frontends');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_frontends($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $check = $this->get_frontend($id);
                if ($check['provider_id'] === $this->session->userdata('client_uid')
                    || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']) {
                    $this->db_master->delete('lib_frontends', array('id' => $id));
                    $this->db_master->delete('lib_clip_frontends', array('frontend_id' => $id));
                }
            }
        }
    }

    function change_status($id){
        if($id){
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_frontends', array('status' => 1));
        }
    }

    function add_sites($data) {
        if (isset($data)) {
            $this->db_master->insert('lib_frontends', $data);
            return $this->db_master->insert_id();
        }
        return false;
    }

    function edit_sites($id, $data) {
        if (isset($data) && !empty($id)) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_frontends', $data);
            //echo $this->db->last_query();exit;
            return true;
        }
        return false;
    }
    
    function delete_site($ids){
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete(
                        'lib_frontends', array('id' => $id), 1
                );
            }
        }
    }

}