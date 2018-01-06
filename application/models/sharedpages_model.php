<?php

class SharedPages_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_shared_pages_count() {
        return $this->db->count_all('lib_shared_pages');
    }

    function get_shared_pages_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_shared_pages');
        $res = $query->result_array();
        return $res;
    }

    function save_shared_page($id){
        $data = $this->input->post();
        if(!$data['url'] && $data['title'])
            $data['url'] = url_title($data['title'], 'dash', TRUE);
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_shared_pages', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_shared_pages', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_shared_page($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_shared_pages');
        $res = $query->result_array();
        return $res[0];
    }

    function get_shared_page_by_url($url){
        $this->db->where('url', $url);
        $this->db->where('status', 1);
        $query = $this->db->get('lib_shared_pages');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_shared_pages($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_shared_pages', array('id' => $id));
            }
        }
    }

    function change_visible($ids) {
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->query('UPDATE lib_shared_pages SET status = !status where id = ' . $id);
            }
        }
    }

    function get_shared_pages_types(){
        $types = array();
        $this->db->select('type');
        $this->db->distinct('type');
        $query = $this->db->get('lib_shared_pages');
        $res = $query->result_array();
        if($res)
            foreach($res as $item){
                $types[] = $item['type'];
            }

        return $types;
    }
}