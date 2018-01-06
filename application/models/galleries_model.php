<?php

class galleries_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_galleries_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_backend_lb');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_backend_lb');
    }

    function get_galleries_list($filter = array(), $limit = array(), $order_by = ''){
        $this->load->model('clips_model');
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_backend_lb');
        $res = $query->result_array();

        // If don't thumb gallery, get thumb first clip
        foreach($res as $k=>$gallery){
            $preview_code=$this->db->query('SELECT c.id, c.code, c.original_filename FROM lib_backend_lb_items AS i INNER JOIN lib_clips AS c ON i.item_id=c.id WHERE backend_lb_id ='.(int)$gallery['id'].' ORDER BY c.id DESC LIMIT 1')->result_array();
            if(empty($preview_code)){
                unset($res[$k]);
            }elseif(empty($gallery['preview_clip']) || $gallery['preview_clip']=='/backend-content/profiles/no-photo.jpg'){
                $res[$k]['preview_clip']=$this->clips_model->get_clip_path($preview_code[0]['id'],'thumb');
            }
        }
        return $res;
    }

    function save_gallery($id){
        $data = $this->input->post();
        unset($data['add_selected_clips']);
        if(isset($data['clips'])){
            $clips = explode(',', $data['clips']);
            unset($data['clips']);
        }
        else{
            $clips = array();
        }
        $data['featured'] = isset($data['featured']) ? 1 : 0;
        if($this->session->userdata('client_uid')) {
            $data['provider_id'] = $this->session->userdata('client_uid');
        }
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_galleries', $data);
        }
        else {
            $this->db_master->insert('lib_galleries', $data);
            $id = $this->db_master->insert_id();
        }
        if($clips)
            $this->add_items($id, $clips);
        return $id;
    }

    function get_gallery($id){
        $this->db->select('g.*, um.meta_value as company_name');
        $this->db->join('lib_users_meta um', 'g.client_id = um.user_id AND um.meta_key="company_name"', 'left');
        $this->db->where('g.id', $id);
        $this->db->where('g.is_gallery', 1);
        $query = $this->db->get('lib_backend_lb g'/*'lib_galleries'*/);
        $res = $query->result_array();
        return $res[0];
    }

    function delete_galleries($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $check = $this->get_gallery($id);
                if ($check['provider_id'] === $this->session->userdata('client_uid')
                    || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']) {
                    $this->db_master->delete('lib_galleries', array('id' => $id));
                    $this->db_master->delete('lib_clip_galleries', array('gallery_id' => $id));
                }
            }
        }
    }

    function get_gallery_clips($id){
        $this->db->select('c.id, c.code, cr.resource');
        $this->db->from('lib_clips c');
        $this->db->join('lib_clips_res cr', 'c.id = cr.clip_id AND cr.resource = \'jpg\' AND cr.type = 0', 'left');
        $this->db->join('lib_clip_galleries cg', 'c.id = cg.clip_id AND cg.gallery_id = ' . (int)$id);
        $query = $this->db->get();
        $res = $query->result_array();
        $base_url = $this->config->base_url();
        if($res)
            foreach($res as &$item){
                $item['thumb'] = rtrim($base_url, '/') . '/' . $this->config->item('clip_path') . 'thumb/' . $item['id'] . '.' . $item['resource'];
            }
        return $res;
    }

    function get_gallery_thumb($gallery){
        $thumb = false;
        if($gallery['preview_clip_id']){
            $this->db->select('resource');
            $this->db->where(array(
                'clip_id' => $gallery['preview_clip_id'],
                'resource' => 'jpg'
            ));
            $query = $this->db->get('lib_clips_res');
            $res = $query->result_array();
            $base_url = $this->config->base_url();
            if($res[0])
                $thumb = rtrim($base_url, '/') . '/' . $this->config->item('clip_path') . 'thumb/' . $gallery['preview_clip_id'] . '.' . $res[0]['resource'];
        }
        else{
            $gallery_clips = $this->get_gallery_clips($gallery['id']);
            if(count($gallery_clips))
                $thumb = $gallery_clips[0]['thumb'];
        }
        return $thumb;
    }

    function add_items($id, $items_ids){
        if($items_ids && is_array($items_ids)){
            foreach($items_ids as $item_id){
                $this->db_master->delete('lib_clip_galleries', array('gallery_id' => $id, 'clip_id' => $item_id));
                $this->db_master->insert('lib_clip_galleries', array('gallery_id' => $id, 'clip_id' => $item_id));
            }
        }
    }
}