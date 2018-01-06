<?php

/**
 * Class Upload_tokens_model
 * @property  CI_DB_active_record $db_master
 */
class Upload_tokens_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_tokens_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_upload_tokens');
            return $this->db->count_all_results();
        }
        else{
            return $this->db->count_all_results('lib_upload_tokens');
        }
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

        $this->db->join('lib_labs', 'lib_labs.id=lab_id', 'LEFT');
        $this->db->select('lib_upload_tokens.*, lib_labs.name as lab_name');
        $query = $this->db->get('lib_upload_tokens');
        $res = $query->result_array();
        return $res;
    }

    function save_token($id){
        $data = $this->input->post();
        if($this->session->userdata('uid')) {
            $data['provider_id'] = $this->session->userdata('uid');
        }
        $data['order_id'] = preg_replace("/[^0-9]/", "", $data['path']);
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_upload_tokens', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_upload_tokens', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_token($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_upload_tokens');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_tokens($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_upload_tokens', array('id' => $id));
            }
        }
    }

    function change_visible($ids) {
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->query('UPDATE lib_download_tokens SET is_active = !is_active where id = ' . $id);
            }
        }
    }

    function get_token_path($token){
        $this->db->select('path');
        $this->db->where('token', $token);
        $query = $this->db->get('lib_upload_tokens');
        $res = $query->result_array();
        if($res[0]['path'])
            return $res[0]['path'];
        else
            return false;
    }

    function get_token_with_link($id){
        $link = '';
        $query = $this->db->query('
            SELECT ut.*, f.host_name
            FROM lib_upload_tokens ut
            INNER JOIN lib_orders o ON ut.order_id=o.id
            INNER JOIN lib_users u ON o.client_id = u.id
            INNER JOIN lib_frontends f ON IF(o.frontend_id <> 0, o.frontend_id = f.id, u.provider_id = f.provider_id)
            WHERE ut.id = ' . $id);

        $rows = $query->result_array();
        if(is_array($rows[0])){
            $rows[0]['link'] = 'http://' . $rows[0]['host_name'] . '/orders?action=uploads&token=' . $rows[0]['token'];
            return $rows[0];
        }
        return false;
    }

    function get_tokens_by_uid($uid){
        $this->db->select('lib_upload_tokens.*, lib_frontends.host_name');
        $this->db->from('lib_upload_tokens');
        $this->db->join('lib_labs_users', 'lib_labs_users.lab_id=lib_upload_tokens.lab_id');
        $this->db->join('lib_frontends', 'lib_frontends.id=lib_upload_tokens.frontend_id');
        $this->db->where('lib_labs_users.user_id', $uid);
        $query = $this->db->get();
        $res = $query->result_array();
        return $res;
    }

    function generate_token($order_id, $path, $lab_id, $frontend_id){
        $token = md5($path);
        $this->db_master->set('token', $token);
        $this->db_master->set('path', $path);
        $this->db_master->set('order_id', $order_id);
        $this->db_master->set('lab_id', $lab_id);
        $this->db_master->set('frontend_id', $frontend_id);
        $this->db_master->insert('lib_upload_tokens');
        return $this->db_master->insert_id();
    }

    function get_token_by_order_id($order_id){
        $token = $this->db->query("SELECT token FROM lib_upload_tokens WHERE order_id = '".$order_id."' LIMIT 1")->result_array();
        return $token[0]['token'];
    }

}