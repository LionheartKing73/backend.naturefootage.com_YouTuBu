<?php

class BrowsePages_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_browse_pages_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_browse_pages');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_browse_pages');
    }

    function get_browse_pages_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_browse_pages');
        $res = $query->result_array();
        return $res;
    }

    function save_browse_page($id){
        $data = $this->input->post();
        if($this->session->userdata('client_uid')) {
            $data['provider_id'] = $this->session->userdata('client_uid');
        }
        if(!$data['url'] && $data['title'])
            $data['url'] = url_title($data['title'], 'dash', TRUE);
        $data['video_autoplay'] = isset($data['video_autoplay']) ? 1 : 0;
        $data['video_looping'] = isset($data['video_looping']) ? 1 : 0;
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_browse_pages', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_browse_pages', $data);
            return $this->db_master->insert_id();
        }
    }

    function save_lists($id, $ids) {
        $this->db_master->delete('lib_browse_page_lists', array('page_id' => $id));

        foreach ((array) $ids as $item_id) {
            if($item_id){
                $data['page_id'] = $id;
                $data['list_id'] = $item_id;
                $this->db_master->insert('lib_browse_page_lists', $data);
            }
        }
    }

    function get_browse_page($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_browse_pages');
        $res = $query->result_array();
        return $res[0];
    }

    function get_browse_page_by_url($url){
        $this->db->where('url', $url);
        $this->db->where('status', 1);
        $query = $this->db->get('lib_browse_pages');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_browse_pages($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_browse_pages', array('id' => $id));
            }
        }
    }

    function change_visible($ids) {
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->query('UPDATE lib_browse_pages SET status = !status where id = ' . $id);
            }
        }
    }
}