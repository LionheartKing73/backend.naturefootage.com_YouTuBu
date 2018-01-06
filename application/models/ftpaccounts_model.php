<?php

class FtpAccounts_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_ftpaccounts_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('ftpuser');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('ftpuser');
    }

    function get_ftpaccounts($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('ftpuser');
        $res = $query->result_array();
        return $res;
    }

    function save_ftpaccount($id){
        $data = $this->input->post();
        if($this->session->userdata('client_uid')) {
            $data['provider_id'] = $this->session->userdata('client_uid');
        }
        unset($data['save'], $data['id']);
        $data['uid'] = '1009';
        $data['gid'] = '1009';
        $data['homedir'] = '';
        if($data['order_id']){
            $data['homedir'] = $this->get_order_path($data['order_id']);
        }

        if(!$data['homedir']){
            $data['homedir'] = $this->get_user_path($data['userid']);
        }

        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('ftpuser', $data);
            return $id;
        }
        else {
            $this->db_master->insert('ftpuser', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_ftpaccount_by_userid($id){
        $this->db->where('userid', $id);
        $query = $this->db->get('ftpuser');
        $res = $query->result_array();
        return $res[0];
    }

    function get_store_details(){
        $store = array();
        require(__DIR__ . '/../config/store.php');
        return  $store;
    }

    function get_ftpaccount($id){
        $this->db->where('id', $id);
        $query = $this->db->get('ftpuser');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_ftpaccounts($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('ftpuser', array('id' => $id));
            }
        }
    }

    function change_visible($ids) {
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->query('UPDATE ftpuser SET status = !status where id = ' . $id);
            }
        }
    }

    private function get_order_path($id){
        $path = '';
        $this->db->select('u.login');
        $this->db->from('lib_orders o');
        $this->db->join('lib_users u', 'o.client_id = u.id');
        $this->db->where('o.id', $id);
        $query = $this->db->get();
        $rows = $query->result_array();
        if($rows && $rows[0]['login']){
            $store = array();
            require(__DIR__ . '/../config/store.php');
            $path = $store['user_delivery']['path'] . '/' . $rows[0]['login'] . '/order' . $id;
        }
        return $path;
    }

    private function get_user_path($login){
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $path = $store['user_delivery']['path'] . '/' . $login;
        return $path;
    }
}