<?php

class BrowseLists_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_browse_lists_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_browse_lists');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_browse_lists');
    }

    function get_browse_lists($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_browse_lists');
        $res = $query->result_array();
        return $res;
    }

    function save_browse_list($id){
        $data = $this->input->post();
        if($this->session->userdata('client_uid')) {
            $data['provider_id'] = $this->session->userdata('client_uid');
        }
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_browse_lists', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_browse_lists', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_browse_list($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_browse_lists');
        $res = $query->result_array();
        return $res[0];
    }

    function get_browse_list_by_url($url){
        $this->db->where('url', $url);
        $this->db->where('status', 1);
        $query = $this->db->get('lib_browse_lists');
        $res = $query->result_array();
        return $res[0];
    }

    function get_browse_page_lists($id) {
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        $filter = '';
        if($group['is_editor'] && $uid){
            $filter = ' WHERE l.provider_id = ' . (int)$uid;
        }
        $query = $this->db->query('SELECT l.*, pl.page_id checked FROM lib_browse_lists l
            LEFT JOIN lib_browse_page_lists pl ON l.id = pl.list_id AND pl.page_id = ?' . $filter, array($id));
        $rows = $query->result_array();
        return $rows;
    }

    function delete_browse_lists($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_browse_lists', array('id' => $id));
            }
        }
    }

    function change_visible($ids) {
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->query('UPDATE lib_browse_lists SET status = !status where id = ' . $id);
            }
        }
    }
}