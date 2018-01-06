<?php
class Rss_model extends CI_Model {

    function Rss_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    #------------------------------------------------------------------------------------------------
    
    function get_channels_list()
    {
        $query = $this->db->query('select * from lib_rss');
        return $query->result_array();
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function delete_channels($ids)
    {    
        if(count($ids)){
            foreach($ids as $id){
               $this->db_master->delete('lib_rss', array('id'=>$id));
            }
        }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_channel($id=0)
    {  
        $query = $this->db->query('select * from lib_rss where id='.intval($id));
        $row = $query->result_array();
        $row[0]['lg'] = $row[0]['lang'];
        
        return $row[0]; 
    }    
    
    #------------------------------------------------------------------------------------------------
    
    function save_channel($id)
    {
        $data_content['title'] = $this->input->post('title');
        $data_content['url'] = $this->input->post('url'); 
        $data_content['lang'] = $this->input->post('lang');
            
        if($id){
           $this->db_master->where('id', $id);
           $this->db_master->update('lib_rss', $data_content);
        }
        else   
           $this->db_master->insert('lib_rss', $data_content);
    } 
}
