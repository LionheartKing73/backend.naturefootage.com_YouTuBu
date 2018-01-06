<?php
class News_model extends CI_Model {

    var $news_path;
    var $news_dir;

    function News_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->news_dir = $this->config->item('news_dir');
        $this->news_path = $this->config->item('news_path');
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_news_count($lang, $filter)
    {
        $query = $this->db->query('select lp.id from lib_news as lp left join lib_news_content as lpc on lp.id=lpc.news_id and lpc.lang='.$this->db->escape($lang).' where lp.id>0 '.$filter);
        return $query->num_rows();
    }

    #------------------------------------------------------------------------------------------------
    
    function get_news_list($lang, $filter, $order, $limit)
    {
        $query = $this->db->query('select lp.*, lpc.title, lpc.annotation, DATE_FORMAT(lp.ctime, \'%d.%m.%Y %T\') as ctime from lib_news as lp left join lib_news_content as lpc on lp.id=lpc.news_id and lpc.lang='.$this->db->escape($lang).' where lp.id>0 '.$filter.$order.$limit);
        $rows = $query->result_array();
        if ($rows) {
            foreach ($rows as &$row) {
                $row['thumb'] = $this->get_image_path($row);
            }
        }
        return $rows;
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function change_visible($ids)
    {     
        if(count($ids)){      
            foreach($ids as $id){
               $this->db_master->query('UPDATE lib_news set active = !active where id='.$id);
            }
        }
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function delete_news($ids)
    {    
        if(count($ids)){
            foreach($ids as $id){
               $this->delete_image($id);
               $this->db_master->delete('lib_news', array('id'=>$id));
               $this->db_master->delete('lib_news_content', array('news_id'=>$id));
            }
        }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_news($id, $lang)
    {  
        $query = $this->db->query('select lp.active, lp.resource, lpc.* from lib_news as lp left join lib_news_content as lpc on lp.id=lpc.news_id and lpc.lang='.$this->db->escape($lang).' where lp.id='.intval($id));
        $row = $query->result_array();
        return $row[0]; 
    }    
    
    #------------------------------------------------------------------------------------------------
    
    function save_news($id, $lang)
    {
        $data_content['news_id'] = $id;
        $data_content['lang'] = $lang;
        $data_content['title'] = $this->input->post('title');
        $data_content['annotation'] = $this->input->post('annotation'); 
        $data_content['body'] = $this->input->post('body');
        $data_content['meta_title'] = $this->input->post('meta_title');
        $data_content['meta_desc'] = $this->input->post('meta_desc');
        $data_content['meta_keys'] = $this->input->post('meta_keys');
            
        if($id){
           $query = $this->db->get_where('lib_news_content', array('news_id'=>$id,'lang'=>$lang));
           $row = $query->result_array();
              
           if(count($row)){
              $this->db_master->where('id', $row[0]['id']);
              $this->db_master->update('lib_news_content', $data_content);
           }
           else
              $this->db_master->insert('lib_news_content', $data_content);
        }
        else{   
           $data_news['ctime'] = date('Y-m-d H:i:s');  
           $this->db_master->insert('lib_news', $data_news);
           $id = $this->db_master->insert_id();
  
           $data_content['news_id'] = $this->db_master->insert_id();
           $this->db_master->insert('lib_news_content', $data_content);
        }

        return $id;
    }


    function upload_image($news_id) {
        if(is_uploaded_file($_FILES['mimg']['tmp_name'])) {
            $ext = $this->api->get_file_ext($_FILES['mimg']['name']);

            if($this->api->check_ext($ext,'img')) {
                @copy($_FILES['mimg']['tmp_name'], $this->news_dir.$news_id.'.'.$ext);
                $this->update_resource($news_id, $ext);
            }
            else {
                $this->errors = $this->lang->line('incorrect_image');
            }

            $this->api->log('log_news_upload', $news_id);
        }
    }

    function update_resource($id, $resource = '') {
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_news', array('resource' => $resource));
    }

    function get_image_path($data) {
        if($data['resource']) {
            return $this->news_path . $data['id'] . '.' . $data['resource'];
        }
        else {
            return '';
        }
    }

    function delete_image($id) {

        $query = $this->db->get_where('lib_news', array('id' => $id));
        $row = $query->result_array();
        $row = $row[0];

        @unlink($this->news_dir.$id.'.'.$row['resource']);
        $this->update_resource($id);

        $this->api->log('log_news_unlink', $id);
    }
}
